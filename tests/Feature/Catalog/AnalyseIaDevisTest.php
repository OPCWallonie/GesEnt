<?php

namespace Tests\Feature\Catalog;

use App\Models\CatalogProduit;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDocument;
use App\Models\ParametresEntreprise;
use App\Models\User;
use App\Services\Ia\LlmClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalyseIaDevisTest extends TestCase
{
    use RefreshDatabase;

    private User  $user;
    private Devis $devis;
    private int   $produitId;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        ParametresEntreprise::firstOrCreate(['id' => 1], [
            'nom'                              => 'Test',
            'volatilite_active'                => true,
            'volatilite_seuil_ligne_devis_eur' => 50.0,
            'ai_provider'                      => 'claude',
            'ai_api_key'                       => encrypt('dummy-key'),
            'ai_model'                         => 'claude-haiku-4-5-20251001',
        ]);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        [$this->devis, $this->produitId] = $this->creerDevisAvecProduitVolatil();
    }

    public function test_endpoint_analyser_refuse_si_ia_non_configuree(): void
    {
        ParametresEntreprise::instance()->update(['ai_provider' => null]);

        $response = $this->actingAs($this->user)
            ->post(route('devis.analyser-ia', $this->devis));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_endpoint_analyser_renvoie_info_si_aucun_produit_a_enjeu(): void
    {
        // Devis avec produit stable
        $client = Client::create(['nom' => 'Stable', 'actif' => true]);
        $devis  = Devis::create([
            'numero'        => 'DEV-STABLE-' . uniqid(),
            'client_id'     => $client->id,
            'statut'        => 'brouillon',
            'date_document' => now(),
        ]);
        $produit = CatalogProduit::create([
            'fournisseur'               => 'wasco',
            'reference'                 => 'STAB-' . uniqid(),
            'designation'               => 'Produit stable',
            'prix_catalogue'            => 10.0,
            'prix_revente'              => 10.0,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'volatilite_classe'         => 'stable',
            'volatilite_calculee_at'    => now(),
        ]);
        LigneDocument::create([
            'documentable_type'  => Devis::class,
            'documentable_id'    => $devis->id,
            'catalog_produit_id' => $produit->id,
            'ordre'              => 1,
            'est_section'        => false,
            'designation'        => $produit->designation,
            'unite'              => $produit->unite,
            'quantite'           => 5,
            'prix_unitaire'      => 10.0,
            'remise_valeur'      => 0,
            'remise_type'        => 'montant',
            'taux_tva'           => 21,
            'montant_ht'         => 50.0,
        ]);

        $this->mock(LlmClientService::class, function ($mock) {
            $mock->shouldNotReceive('appeler');
        });

        $response = $this->actingAs($this->user)
            ->post(route('devis.analyser-ia', $devis));

        $response->assertRedirect();
        $response->assertSessionHas('info');
        $this->assertDatabaseMissing('devis_analyses_ia', ['devis_id' => $devis->id]);
    }

    public function test_endpoint_analyser_cree_analyse_si_produit_a_enjeu(): void
    {
        $produitId = $this->produitId;
        $this->mockLlm($produitId, 'once');

        $response = $this->actingAs($this->user)
            ->post(route('devis.analyser-ia', $this->devis));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('devis_analyses_ia', ['devis_id' => $this->devis->id]);
    }

    public function test_endpoint_analyser_utilise_cache_si_lignes_inchangees(): void
    {
        $produitId = $this->produitId;

        // Premier appel — LLM appelé une seule fois
        $this->mockLlm($produitId, 'once');
        $this->actingAs($this->user)->post(route('devis.analyser-ia', $this->devis));

        // Deuxième appel sans modification des lignes — le mock attend toujours 1 appel total
        $this->mockLlm($produitId, 'never');
        $this->actingAs($this->user)->post(route('devis.analyser-ia', $this->devis));

        $this->assertDatabaseHas('devis_analyses_ia', ['devis_id' => $this->devis->id]);
    }

    public function test_endpoint_analyser_invalide_cache_si_quantite_ligne_change(): void
    {
        $produitId = $this->produitId;
        $reponse   = $this->llmReponse($produitId);

        // Le LLM doit être appelé 2 fois au total (cache invalide après modif)
        $this->mock(LlmClientService::class, function ($mock) use ($reponse) {
            $mock->shouldReceive('appeler')->twice()->andReturn($reponse);
        });

        // Premier appel — LLM appelé (1 fois)
        $this->actingAs($this->user)->post(route('devis.analyser-ia', $this->devis));

        // Modifier la quantité d'une ligne → invalide le hash
        $this->devis->load('lignes');
        $this->devis->lignes->first()->update(['quantite' => 99.0, 'montant_ht' => 9900.0]);

        // Deuxième appel — cache invalide → LLM rappelé (2e fois)
        $this->actingAs($this->user)->post(route('devis.analyser-ia', $this->devis));
    }

    public function test_endpoint_invalider_supprime_cache(): void
    {
        $produitId = $this->produitId;
        $this->mockLlm($produitId, 'once');
        $this->actingAs($this->user)->post(route('devis.analyser-ia', $this->devis));

        $this->assertDatabaseHas('devis_analyses_ia', ['devis_id' => $this->devis->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('devis.analyser-ia.invalider', $this->devis));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('devis_analyses_ia', ['devis_id' => $this->devis->id]);
    }

    // ── Helpers ──

    private function creerDevisAvecProduitVolatil(): array
    {
        $produit = CatalogProduit::create([
            'fournisseur'               => 'vanmarke',
            'reference'                 => 'IA-' . uniqid(),
            'designation'               => 'Produit volatil IA',
            'prix_catalogue'            => 100.0,
            'prix_revente'              => 100.0,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'volatilite_classe'         => 'b',
            'volatilite_tendance_pct'   => 15.0,
            'volatilite_calculee_at'    => now(),
        ]);

        $client = Client::create(['nom' => 'Client IA', 'actif' => true]);
        $devis  = Devis::create([
            'numero'        => 'DEV-IA-' . uniqid(),
            'client_id'     => $client->id,
            'statut'        => 'brouillon',
            'date_document' => now(),
        ]);

        LigneDocument::create([
            'documentable_type'  => Devis::class,
            'documentable_id'    => $devis->id,
            'catalog_produit_id' => $produit->id,
            'ordre'              => 1,
            'est_section'        => false,
            'designation'        => $produit->designation,
            'unite'              => $produit->unite,
            'quantite'           => 2,
            'prix_unitaire'      => 100.0,
            'remise_valeur'      => 0,
            'remise_type'        => 'montant',
            'taux_tva'           => 21,
            'montant_ht'         => 200.0,
        ]);

        return [$devis, $produit->id];
    }

    private function llmReponse(int $produitId): array
    {
        return [
            'contenu'       => json_encode([
                'synthese'        => 'Test synthèse.',
                'niveau_alerte'   => 'attention',
                'recommandations' => [[
                    'catalog_produit_id'   => $produitId,
                    'designation'          => 'Produit volatil IA',
                    'action_suggeree'      => 'stocker',
                    'justification'        => 'Prix en hausse. Stocker maintenant.',
                    'economie_estimee_eur' => 50.0,
                ]],
            ]),
            'duree_ms'      => 1200,
            'tokens_entree' => 400,
            'tokens_sortie' => 100,
            'modele'        => 'claude-haiku-4-5-20251001',
            'provider'      => 'claude',
        ];
    }

    private function mockLlm(int $produitId, string $times): void
    {
        $reponse = $this->llmReponse($produitId);

        $this->mock(LlmClientService::class, function ($mock) use ($reponse, $times) {
            $expectation = $mock->shouldReceive('appeler');
            match ($times) {
                'once'  => $expectation->once()->andReturn($reponse),
                'never' => $expectation->never(),
                default => $expectation->andReturn($reponse),
            };
        });
    }
}
