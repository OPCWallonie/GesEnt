<?php

namespace Tests\Feature\Catalog;

use App\Events\CatalogProduitsImportes;
use App\Models\CatalogPrixHistorique;
use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\VolatiliteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VolatiliteServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private VolatiliteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(VolatiliteService::class);
        ParametresEntreprise::firstOrCreate(['id' => 1], ['nom' => 'Test']);
    }

    public function test_recalculer_produit_persiste_les_colonnes(): void
    {
        $produit = $this->creerProduitAvecHistorique('vanmarke', 'TEST-001', 8.50, [
            ['mois' => 22, 'pct' => 4.5],
            ['mois' => 20, 'pct' => 5.0],
            ['mois' => 18, 'pct' => 4.8],
            ['mois' => 16, 'pct' => 5.0],
            ['mois' => 12, 'pct' => 5.0],
        ], 5.00);

        $this->service->recalculerProduit($produit);

        $produit->refresh();
        $this->assertNotNull($produit->volatilite_classe);
        $this->assertNotNull($produit->volatilite_calculee_at);
        $this->assertNotNull($produit->volatilite_amplitude_pct);
    }

    public function test_recalculer_produits_traite_plusieurs(): void
    {
        $p1 = $this->creerProduitAvecHistorique('vanmarke', 'MULTI-001', 110.0, [
            ['mois' => 10, 'pct' => 3.0],
            ['mois' =>  6, 'pct' => 4.0],
            ['mois' =>  3, 'pct' => 2.0],
        ], 100.0);

        $p2 = $this->creerProduitAvecHistorique('vanmarke', 'MULTI-002', 115.0, [
            ['mois' => 12, 'pct' => 5.0],
            ['mois' =>  6, 'pct' => 5.0],
            ['mois' =>  2, 'pct' => 4.0],
        ], 100.0);

        $nb = $this->service->recalculerProduits(collect([$p1, $p2]));
        $this->assertEquals(2, $nb);

        $p1->refresh();
        $p2->refresh();
        $this->assertNotNull($p1->volatilite_calculee_at);
        $this->assertNotNull($p2->volatilite_calculee_at);
    }

    public function test_compareur_ean_retourne_produits_partageant_un_ean(): void
    {
        $ean = '1234567890123';

        $p1 = CatalogProduit::create([
            'fournisseur'         => 'vanmarke',
            'reference'           => 'EAN-001',
            'designation'         => 'Produit EAN 1',
            'prix_catalogue'      => 10.00,
            'prix_revente'        => 10.00,
            'taux_tva'            => 21,
            'unite'               => 'pièce',
            'ean'                 => $ean,
            'volatilite_calculee_at' => now(),
        ]);
        $p2 = CatalogProduit::create([
            'fournisseur'         => 'wasco',
            'reference'           => 'EAN-002',
            'designation'         => 'Produit EAN 2',
            'prix_catalogue'      => 9.50,
            'prix_revente'        => 9.50,
            'taux_tva'            => 21,
            'unite'               => 'pièce',
            'ean'                 => $ean,
            'volatilite_calculee_at' => now(),
        ]);
        // Ce produit n'a pas d'EAN identique
        CatalogProduit::create([
            'fournisseur'         => 'desco',
            'reference'           => 'AUTRE-001',
            'designation'         => 'Autre produit',
            'prix_catalogue'      => 5.00,
            'prix_revente'        => 5.00,
            'taux_tva'            => 21,
            'unite'               => 'pièce',
            'ean'                 => '9999999999999',
            'volatilite_calculee_at' => now(),
        ]);

        $resultats = $this->service->compareurEan($ean);
        $this->assertCount(2, $resultats);
        $ids = $resultats->pluck('id')->all();
        $this->assertContains($p1->id, $ids);
        $this->assertContains($p2->id, $ids);
    }

    public function test_event_catalog_produits_importes_declenche_listener(): void
    {
        $produit = CatalogProduit::create([
            'fournisseur'    => 'vanmarke',
            'reference'      => 'EVENT-001',
            'designation'    => 'Produit événement',
            'prix_catalogue' => 10.00,
            'prix_revente'   => 10.00,
            'taux_tva'       => 21,
            'unite'          => 'pièce',
        ]);

        $this->creerHistoriquePourProduit($produit, 8.00, [
            ['mois' => 10, 'pct' => 5.0],
            ['mois' =>  6, 'pct' => 5.0],
            ['mois' =>  2, 'pct' => 5.0],
        ]);

        event(new CatalogProduitsImportes(
            produitIds: [$produit->id],
            source: 'csv',
        ));

        $produit->refresh();
        $this->assertNotNull($produit->volatilite_calculee_at);
    }

    public function test_flag_toujours_alerter_force_signaux_meme_si_insuffisant(): void
    {
        $produit = CatalogProduit::create([
            'fournisseur'            => 'vanmarke',
            'reference'              => 'FLAG-TOUJOURS-001',
            'designation'            => 'Produit flag toujours alerter',
            'prix_catalogue'         => 10.00,
            'prix_revente'           => 10.00,
            'taux_tva'               => 21,
            'unite'                  => 'pièce',
            'volatilite_flag_manuel' => 'toujours_alerter',
        ]);
        // Aucun historique → classe sera 'insuffisant'

        $this->service->recalculerProduit($produit);

        $produit->refresh();
        $this->assertSame('insuffisant', $produit->volatilite_classe);
        $this->assertTrue((bool) $produit->volatilite_signal_relatif, 'Signal relatif doit être true via flag toujours_alerter');
        $this->assertTrue((bool) $produit->volatilite_signal_absolu, 'Signal absolu doit être true via flag toujours_alerter');
    }

    public function test_flag_jamais_alerter_force_signaux_false_meme_sur_produit_volatil(): void
    {
        $produit = CatalogProduit::create([
            'fournisseur'            => 'vanmarke',
            'reference'              => 'FLAG-JAMAIS-001',
            'designation'            => 'Produit flag jamais alerter',
            'prix_catalogue'         => 10.00,
            'prix_revente'           => 10.00,
            'taux_tva'               => 21,
            'unite'                  => 'pièce',
            'volatilite_flag_manuel' => 'jamais_alerter',
        ]);

        // Historique de forte hausse → tendance >> garde-fou absolu (15%)
        $this->creerHistoriquePourProduit($produit, 5.00, [
            ['mois' => 22, 'pct' => 5.0],
            ['mois' => 18, 'pct' => 5.0],
            ['mois' => 14, 'pct' => 5.0],
            ['mois' => 10, 'pct' => 5.0],
            ['mois' =>  6, 'pct' => 5.0],
            ['mois' =>  2, 'pct' => 5.0],
        ]);

        $this->service->recalculerProduit($produit);

        $produit->refresh();
        $this->assertNotEquals('insuffisant', $produit->volatilite_classe);
        $this->assertFalse((bool) $produit->volatilite_signal_relatif, 'Signal relatif doit être false via flag jamais_alerter');
        $this->assertFalse((bool) $produit->volatilite_signal_absolu, 'Signal absolu doit être false via flag jamais_alerter');
    }

    public function test_module_inactif_ne_recalcule_pas(): void
    {
        $params = ParametresEntreprise::instance();
        $params->update(['volatilite_active' => false]);

        $produit = $this->creerProduitAvecHistorique('vanmarke', 'INACTIF-001', 110.0, [
            ['mois' => 6, 'pct' => 5.0],
            ['mois' => 3, 'pct' => 5.0],
            ['mois' => 1, 'pct' => 5.0],
        ], 100.0);

        $this->service->recalculerProduit($produit);
        $produit->refresh();
        $this->assertNull($produit->volatilite_calculee_at);

        // Remettre le module actif pour les autres tests
        $params->update(['volatilite_active' => true]);
    }

    // ── Helpers ──

    private function creerProduitAvecHistorique(
        string $fournisseur,
        string $reference,
        float $prixActuel,
        array $variations,
        float $prixDepart
    ): CatalogProduit {
        $produit = CatalogProduit::create([
            'fournisseur'    => $fournisseur,
            'reference'      => $reference,
            'designation'    => "Test {$reference}",
            'prix_catalogue' => $prixActuel,
            'prix_revente'   => $prixActuel,
            'taux_tva'       => 21,
            'unite'          => 'pièce',
        ]);

        $this->creerHistoriquePourProduit($produit, $prixDepart, $variations);
        return $produit;
    }

    private function creerHistoriquePourProduit(CatalogProduit $produit, float $prixDepart, array $variations): void
    {
        $prixCourant = $prixDepart;
        foreach ($variations as $v) {
            $prixAvant = $prixCourant;
            $prixApres = round($prixAvant * (1 + $v['pct'] / 100), 4);
            CatalogPrixHistorique::create([
                'catalog_produit_id' => $produit->id,
                'fournisseur'        => $produit->fournisseur,
                'reference'          => $produit->reference,
                'prix_avant'         => $prixAvant,
                'prix_apres'         => $prixApres,
                'variation_pct'      => $v['pct'],
                'est_significatif'   => abs($v['pct']) >= 3,
                'source'             => 'csv',
                'detected_at'        => now()->subMonths($v['mois']),
            ]);
            $prixCourant = $prixApres;
        }
    }
}
