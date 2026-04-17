<?php

namespace Tests\Feature;

use App\Models\CatalogPrixHistorique;
use App\Models\CatalogProduit;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDocument;
use App\Models\User;
use App\Services\Catalog\DevisImpactService;
use App\Services\Catalog\PrixHistoriqueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrixHistoriqueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    private function createProduit(array $attrs = []): CatalogProduit
    {
        return CatalogProduit::create(array_merge([
            'fournisseur'    => 'desco',
            'reference'      => 'TEST001',
            'designation'    => 'Produit test',
            'prix_catalogue' => 100.00,
            'prix_revente'   => 100.00,
            'taux_tva'       => 21,
        ], $attrs));
    }

    public function test_changement_de_prix_est_historise(): void
    {
        $produit = $this->createProduit(['prix_catalogue' => 100.00]);

        app(PrixHistoriqueService::class)->enregistrerSiChange($produit, 110.00, 'csv');

        $historique = CatalogPrixHistorique::first();
        $this->assertNotNull($historique);
        $this->assertEquals(100.00, (float) $historique->prix_avant);
        $this->assertEquals(110.00, (float) $historique->prix_apres);
        $this->assertEquals(10.00, (float) $historique->variation_pct);
        $this->assertTrue($historique->est_significatif);
    }

    public function test_aucun_historique_si_prix_identique(): void
    {
        $produit = $this->createProduit(['prix_catalogue' => 50.00]);

        app(PrixHistoriqueService::class)->enregistrerSiChange($produit, 50.00, 'csv');

        $this->assertEquals(0, CatalogPrixHistorique::count());
    }

    public function test_variation_sous_seuil_non_significative(): void
    {
        $produit = $this->createProduit(['prix_catalogue' => 100.00]);

        app(PrixHistoriqueService::class)->enregistrerSiChange($produit, 102.00, 'csv');

        $hist = CatalogPrixHistorique::first();
        $this->assertNotNull($hist);
        $this->assertFalse($hist->est_significatif);
    }

    public function test_creation_produit_ne_cree_pas_historique(): void
    {
        app(PrixHistoriqueService::class)->enregistrerSiChange(null, 50.00, 'csv');

        $this->assertEquals(0, CatalogPrixHistorique::count());
    }

    public function test_devis_impact_service_detecte_devis_actifs(): void
    {
        $produit = $this->createProduit(['prix_catalogue' => 100.00]);

        $client = Client::create([
            'nom'   => 'Client test',
            'email' => 'test@test.be',
            'actif' => true,
        ]);

        $devis = Devis::create([
            'numero'        => 'D-2026-001',
            'client_id'     => $client->id,
            'statut'        => 'valide',
            'date_document' => now()->subDays(10)->toDateString(),
            'montant_ht'    => 100,
            'montant_tva'   => 21,
            'montant_ttc'   => 121,
        ]);

        LigneDocument::create([
            'documentable_type' => Devis::class,
            'documentable_id'   => $devis->id,
            'catalog_produit_id' => $produit->id,
            'ordre'             => 1,
            'designation'       => $produit->designation,
            'quantite'          => 1,
            'prix_unitaire'     => 100,
            'taux_tva'          => 21,
            'montant_ht'        => 100,
        ]);

        // Simuler un changement significatif APRÈS la création du devis
        CatalogPrixHistorique::create([
            'catalog_produit_id' => $produit->id,
            'fournisseur'        => 'desco',
            'reference'          => 'TEST001',
            'prix_avant'         => 100.00,
            'prix_apres'         => 108.00,
            'variation_pct'      => 8.00,
            'est_significatif'   => true,
            'source'             => 'csv',
            'detected_at'        => now()->subDays(5),
        ]);

        $impactes = app(DevisImpactService::class)->devisActifsImpactes();

        $this->assertArrayHasKey($devis->id, $impactes);
        $this->assertEquals(1, $impactes[$devis->id]);
    }
}
