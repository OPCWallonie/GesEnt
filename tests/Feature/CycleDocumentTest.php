<?php

namespace Tests\Feature;

use App\Models\BonCommande;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CycleDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Client $client;
    protected Chantier $chantier;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles requis par spatie/laravel-permission
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->client = Client::create([
            'nom'   => 'Test Client SPRL',
            'email' => 'test@example.com',
            'actif' => true,
        ]);

        $this->chantier = Chantier::create([
            'client_id' => $this->client->id,
            'nom'       => 'Chantier test',
            'statut'    => 'actif',
        ]);
    }

    #[Test]
    public function cycle_complet_devis_bdc_facture_situations_paiement(): void
    {
        $this->actingAs($this->admin);

        // 1. Créer un devis
        $response = $this->post(route('devis.store'), [
            'client_id'       => $this->client->id,
            'chantier_id'     => $this->chantier->id,
            'statut'          => 'en_attente',
            'date_document'   => now()->toDateString(),
            'date_validite'   => now()->addDays(30)->toDateString(),
            'delai_reglement' => 30,
            'lignes' => [
                [
                    'designation'   => 'Travaux de maçonnerie',
                    'unite'         => 'm²',
                    'quantite'      => 100,
                    'prix_unitaire' => 85,
                    'taux_tva'      => 21,
                ],
                [
                    'designation'   => 'Fourniture briques',
                    'unite'         => 'pièce',
                    'quantite'      => 500,
                    'prix_unitaire' => 1.20,
                    'taux_tva'      => 21,
                ],
            ],
        ]);

        $response->assertRedirect();
        $devis = Devis::orderByDesc('id')->first();
        $this->assertNotNull($devis);
        $this->assertEquals(2, $devis->lignes->count());
        $this->assertGreaterThan(0, $devis->montant_ttc);

        // 2. Valider le devis
        $devis->update(['statut' => 'valide']);
        $this->assertEquals('valide', (string) $devis->fresh()->statut);

        // 3. Créer le BDC
        $response = $this->post(route('bons-commande.store'), [
            'devis_id'        => $devis->id,
            'client_id'       => $this->client->id,
            'chantier_id'     => $this->chantier->id,
            'statut'          => 'valide',
            'date_document'   => now()->toDateString(),
            'delai_reglement' => 30,
            'lignes' => [
                [
                    'designation'   => 'Travaux de maçonnerie',
                    'unite'         => 'm²',
                    'quantite'      => 100,
                    'prix_unitaire' => 85,
                    'taux_tva'      => 21,
                ],
                [
                    'designation'   => 'Fourniture briques',
                    'unite'         => 'pièce',
                    'quantite'      => 500,
                    'prix_unitaire' => 1.20,
                    'taux_tva'      => 21,
                ],
            ],
        ]);

        $response->assertRedirect();
        $bdc = BonCommande::latest()->first();
        $this->assertNotNull($bdc);
        $this->assertEqualsWithDelta(100, $bdc->pourcentageRestant(), 0.01);

        // 4. Facturer situation 1 (40%)
        $response = $this->post(route('factures.store'), [
            'bon_commande_id'        => $bdc->id,
            'statut'                 => 'en_attente',
            'date_document'          => now()->toDateString(),
            'date_echeance'          => now()->addDays(30)->toDateString(),
            'delai_reglement'        => 30,
            'numero_situation'       => 1,
            'pourcentage_avancement' => 40,
            'lignes' => [
                [
                    'designation'   => 'Situation 1 — Maçonnerie',
                    'unite'         => 'm²',
                    'quantite'      => 40,
                    'prix_unitaire' => 85,
                    'taux_tva'      => 21,
                ],
            ],
        ]);

        $response->assertRedirect();
        $facture1 = Facture::latest()->first();
        $this->assertEquals(1, $facture1->numero_situation);
        $this->assertEquals(40, $facture1->pourcentage_avancement);

        // BDC : il reste encore à facturer
        $bdc->refresh()->load('factures');
        $this->assertGreaterThan(0, $bdc->pourcentageRestant());

        // 5. Paiement partiel (50% de la facture)
        $paiementPartiel = round((float) $facture1->montant_ttc * 0.5, 2);
        $this->patch(route('factures.marquer-payee', $facture1), [
            'date_paiement' => now()->toDateString(),
            'montant_paye'  => $paiementPartiel,
            'mode'          => 'virement',
        ])->assertRedirect();

        $facture1->refresh();
        $this->assertEquals(1, $facture1->paiements->count());
        $this->assertFalse($facture1->est_totalement_payee);

        // 6. Paiement du solde → facture payée
        $this->patch(route('factures.marquer-payee', $facture1), [
            'date_paiement' => now()->toDateString(),
            'montant_paye'  => $facture1->montant_restant,
            'mode'          => 'virement',
        ])->assertRedirect();

        $facture1->refresh();
        $this->assertEquals(2, $facture1->paiements->count());
        $this->assertTrue($facture1->est_totalement_payee);
        $this->assertEquals('payee', (string) $facture1->statut);

        // 7. Rentabilité chantier
        $this->assertGreaterThan(0, $this->chantier->fresh()->totalVentes());
    }

    #[Test]
    public function dupliquer_un_devis_copie_toutes_les_lignes(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('devis.store'), [
            'client_id'       => $this->client->id,
            'statut'          => 'valide',
            'date_document'   => now()->toDateString(),
            'delai_reglement' => 30,
            'lignes' => [
                ['designation' => 'Ligne 1', 'quantite' => 10, 'prix_unitaire' => 50,  'taux_tva' => 21],
                ['designation' => 'Ligne 2', 'quantite' => 5,  'prix_unitaire' => 100, 'taux_tva' => 6],
                ['designation' => 'Section', 'est_section' => true],
                ['designation' => 'Ligne 3', 'quantite' => 1,  'prix_unitaire' => 200, 'taux_tva' => 21],
            ],
        ]);

        $original = Devis::orderByDesc('id')->first();

        $this->post(route('devis.dupliquer', $original))->assertRedirect();

        $copie = Devis::orderByDesc('id')->first();
        $this->assertNotEquals($original->id, $copie->id);
        $this->assertNotEquals($original->numero, $copie->numero);
        $this->assertEquals('brouillon', (string) $copie->statut);
        $this->assertNull($copie->chantier_id);
        $this->assertEquals($original->lignes->count(), $copie->lignes->count());
        $this->assertEquals(
            $original->lignes->pluck('designation')->toArray(),
            $copie->lignes->pluck('designation')->toArray()
        );
    }

    #[Test]
    public function recherche_globale_trouve_par_adresse_et_ville(): void
    {
        $this->actingAs($this->admin);

        Chantier::create([
            'client_id'        => $this->client->id,
            'nom'              => 'Villa Marguerite',
            'adresse_chantier' => 'Rue des Lilas 42',
            'ville'            => 'Liège',
            'statut'           => 'actif',
        ]);

        $response = $this->getJson(route('search', ['q' => 'Lilas 42']));
        $response->assertOk();
        $results = collect($response->json());
        $this->assertTrue($results->contains('type', 'Chantier'));
    }
}
