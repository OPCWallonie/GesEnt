<?php

namespace Tests\Unit\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDocument;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\AnalyseIaDevisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyseIaDevisServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnalyseIaDevisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AnalyseIaDevisService::class);
        ParametresEntreprise::firstOrCreate(['id' => 1], [
            'nom'                              => 'Test',
            'volatilite_active'                => true,
            'volatilite_seuil_ligne_devis_eur' => 50.0,
        ]);
    }

    public function test_hash_lignes_stable_pour_meme_contenu(): void
    {
        $devis = $this->creerDevisAvecLigne('b', 100.0, 2.0);

        $h1 = $this->appelHashLignes($devis);
        $h2 = $this->appelHashLignes($devis);

        $this->assertSame($h1, $h2);
    }

    public function test_hash_lignes_change_si_quantite_change(): void
    {
        $devis = $this->creerDevisAvecLigne('b', 100.0, 2.0);

        $h1 = $this->appelHashLignes($devis);

        $devis->lignes->first()->update(['quantite' => 5.0]);
        $devis->load('lignes');

        $h2 = $this->appelHashLignes($devis);

        $this->assertNotSame($h1, $h2);
    }

    public function test_hash_alternatives_stable_malgre_ordre_alternatives(): void
    {
        $produit1Id = 1;
        $produit2Id = 2;
        $alt1Id     = 10;
        $alt2Id     = 20;

        $alts1 = [
            $produit1Id => collect([
                $this->makeAltStub($alt1Id, 90.0, -0.5),
                $this->makeAltStub($alt2Id, 85.0, -0.8),
            ]),
        ];
        $alts2 = [
            $produit1Id => collect([
                $this->makeAltStub($alt2Id, 85.0, -0.8),
                $this->makeAltStub($alt1Id, 90.0, -0.5),
            ]),
        ];

        $h1 = $this->appelHashAlternatives($alts1);
        $h2 = $this->appelHashAlternatives($alts2);

        $this->assertSame($h1, $h2);
    }

    public function test_parser_reponse_valide(): void
    {
        $json = json_encode([
            'synthese'        => 'Tout va bien.',
            'niveau_alerte'   => 'info',
            'recommandations' => [],
        ]);

        $result = $this->appelParserReponse($json);

        $this->assertSame('Tout va bien.', $result['synthese']);
        $this->assertSame('info', $result['niveau_alerte']);
        $this->assertIsArray($result['recommandations']);
    }

    public function test_parser_reponse_avec_fence_markdown(): void
    {
        $json = "```json\n" . json_encode([
            'synthese'        => 'Test markdown.',
            'niveau_alerte'   => 'attention',
            'recommandations' => [],
        ]) . "\n```";

        $result = $this->appelParserReponse($json);

        $this->assertSame('Test markdown.', $result['synthese']);
    }

    public function test_parser_reponse_avec_texte_avant_apres(): void
    {
        $payload = json_encode([
            'synthese'        => 'Extrait.',
            'niveau_alerte'   => 'info',
            'recommandations' => [],
        ]);
        $texte = "Voici l'analyse : {$payload}\n\nMerci.";

        $result = $this->appelParserReponse($texte);

        $this->assertSame('Extrait.', $result['synthese']);
    }

    public function test_parser_reponse_niveau_alerte_invalide_fallback_info(): void
    {
        $json = json_encode([
            'synthese'        => 'Test.',
            'niveau_alerte'   => 'critique_inconnue',
            'recommandations' => [],
        ]);

        $result = $this->appelParserReponse($json);

        $this->assertSame('info', $result['niveau_alerte']);
    }

    public function test_parser_reponse_action_invalide_fallback_aucune(): void
    {
        $json = json_encode([
            'synthese'        => 'Test.',
            'niveau_alerte'   => 'info',
            'recommandations' => [[
                'catalog_produit_id'   => 1,
                'designation'          => 'Produit X',
                'action_suggeree'      => 'action_inconnue',
                'justification'        => 'Test.',
                'economie_estimee_eur' => 0,
            ]],
        ]);

        $result = $this->appelParserReponse($json);

        $this->assertSame('aucune', $result['recommandations'][0]['action_suggeree']);
    }

    public function test_parser_reponse_json_invalide_lance_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->appelParserReponse('ceci n\'est pas du JSON valide');
    }

    // ── Helpers ──

    private function creerDevisAvecLigne(string $classe, float $prixUnitaire, float $quantite): Devis
    {
        $produit = CatalogProduit::create([
            'fournisseur'               => 'vanmarke',
            'reference'                 => 'HASH-' . uniqid(),
            'designation'               => 'Produit hash test',
            'prix_catalogue'            => $prixUnitaire,
            'prix_revente'              => $prixUnitaire,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'volatilite_classe'         => $classe,
            'volatilite_tendance_pct'   => 10.0,
            'volatilite_calculee_at'    => now(),
        ]);

        $client = Client::create(['nom' => 'Client hash', 'actif' => true]);
        $devis  = Devis::create([
            'numero'        => 'DEV-' . uniqid(),
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
            'quantite'           => $quantite,
            'prix_unitaire'      => $prixUnitaire,
            'remise_valeur'      => 0,
            'remise_type'        => 'montant',
            'taux_tva'           => 21,
            'montant_ht'         => $prixUnitaire * $quantite,
        ]);

        return $devis->load('lignes');
    }

    private function makeAltStub(int $altId, float $prix, float $score): object
    {
        $produit = new \stdClass();
        $produit->id             = $altId;
        $produit->prix_catalogue = $prix;

        $alt                = new \stdClass();
        $alt->produit       = $produit;
        $alt->scoreComposite = $score;
        return $alt;
    }

    private function appelHashLignes(Devis $devis): string
    {
        $ref = new \ReflectionMethod(AnalyseIaDevisService::class, 'hashLignes');
        $ref->setAccessible(true);

        $idsAEnjeu = $devis->lignes->pluck('catalog_produit_id')->filter()->values()->all();
        return $ref->invoke($this->service, $devis, $idsAEnjeu);
    }

    private function appelHashAlternatives(array $alts): string
    {
        $ref = new \ReflectionMethod(AnalyseIaDevisService::class, 'hashAlternatives');
        $ref->setAccessible(true);
        return $ref->invoke($this->service, $alts);
    }

    private function appelParserReponse(string $contenu): array
    {
        $ref = new \ReflectionMethod(AnalyseIaDevisService::class, 'parserReponse');
        $ref->setAccessible(true);
        return $ref->invoke($this->service, $contenu);
    }
}
