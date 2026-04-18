<?php

namespace Tests\Feature;

use App\Models\BonCommande;
use App\Models\CatalogPrixHistorique;
use App\Models\CatalogProduit;
use App\Models\Client;
use App\Models\Facture;
use App\Models\LigneDocument;
use App\Models\Produit;
use App\Services\VentesHistoriqueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VentesHistoriqueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    private function createClient(string $nom = 'Client Test'): Client
    {
        return Client::create(['nom' => $nom, 'email' => strtolower(str_replace(' ', '.', $nom)) . '@test.be', 'actif' => true]);
    }

    private function createBdc(Client $client, string $numero = 'BDC-001', string $dateDoc = '-5 months'): BonCommande
    {
        return BonCommande::create([
            'numero'        => $numero,
            'client_id'     => $client->id,
            'statut'        => 'valide',
            'date_document' => now()->modify($dateDoc)->toDateString(),
        ]);
    }

    private function createLigneBdc(BonCommande $bdc, float $prix, ?CatalogProduit $cp = null, ?Produit $produit = null): LigneDocument
    {
        return LigneDocument::create([
            'documentable_type'  => BonCommande::class,
            'documentable_id'    => $bdc->id,
            'catalog_produit_id' => $cp?->id,
            'produit_id'         => $produit?->id,
            'ordre'              => 1,
            'designation'        => $cp?->designation ?? $produit?->designation ?? 'Produit',
            'unite'              => 'm²',
            'quantite'           => 10,
            'prix_unitaire'      => $prix,
            'taux_tva'           => 21,
            'montant_ht'         => $prix * 10,
        ]);
    }

    /**
     * Cas central : vendu à 45 € quand le catalogue était à 39 €, maintenant à 45 €.
     * Marge d'alors = +15,38% → équivalent aujourd'hui = 45 × 1,1538 ≈ 51,92 €
     */
    public function test_marge_preservee_quand_prix_catalogue_evolue(): void
    {
        $cp = CatalogProduit::create([
            'fournisseur'    => 'desco',
            'reference'      => 'TEST-MARGE',
            'designation'    => 'Dalle 60x60',
            'prix_catalogue' => 45.00,
            'prix_revente'   => 45,
            'taux_tva'       => 21,
        ]);

        // Changement de prix : 39 → 45 le 01/02/2026
        CatalogPrixHistorique::create([
            'catalog_produit_id' => $cp->id,
            'fournisseur'        => 'desco',
            'reference'          => 'TEST-MARGE',
            'prix_avant'         => 39.00,
            'prix_apres'         => 45.00,
            'variation_pct'      => 15.38,
            'est_significatif'   => true,
            'source'             => 'csv',
            'detected_at'        => now()->parse('2026-02-01'),
        ]);

        $client = $this->createClient('Martin SA');

        // BDC du 15/01/2026 (avant le changement) : vendu 45 € avec prix catalogue à 39 €
        $bdc = $this->createBdc($client, 'BDC-MARGE', '2026-01-15');
        $ligne = $this->createLigneBdc($bdc, 45.00, $cp);

        $result = app(VentesHistoriqueService::class)
            ->historique(null, $cp->id, null, $client->id);

        $this->assertCount(1, $result['ventes_ce_client']);
        $vente = $result['ventes_ce_client'][0];

        $this->assertNotNull($vente['contexte_marge']);
        $this->assertEquals(39.00, $vente['contexte_marge']['prix_catalogue_epoque']);
        $this->assertEquals(45.00, $vente['contexte_marge']['prix_catalogue_actuel']);
        $this->assertEqualsWithDelta(15.38, $vente['contexte_marge']['marge_pct_epoque'], 0.1);
        $this->assertEqualsWithDelta(51.92, $vente['contexte_marge']['prix_equivalent_actuel'], 0.1);
    }

    public function test_vente_sans_changement_de_prix_catalogue(): void
    {
        $cp = CatalogProduit::create([
            'fournisseur'    => 'desco',
            'reference'      => 'STABLE',
            'designation'    => 'Produit stable',
            'prix_catalogue' => 50.00,
            'prix_revente'   => 50,
            'taux_tva'       => 21,
        ]);

        $client = $this->createClient();
        $bdc    = $this->createBdc($client, 'BDC-STABLE');
        $this->createLigneBdc($bdc, 60.00, $cp);

        $result = app(VentesHistoriqueService::class)->historique(null, $cp->id, null, $client->id);
        $vente  = $result['ventes_ce_client'][0];

        $this->assertEquals(50.00, $vente['contexte_marge']['prix_catalogue_epoque']);
        $this->assertEquals(50.00, $vente['contexte_marge']['prix_catalogue_actuel']);
        $this->assertEquals(0.00, $vente['contexte_marge']['evolution_catalogue_pct']);
        $this->assertEqualsWithDelta(60.00, $vente['contexte_marge']['prix_equivalent_actuel'], 0.1);
    }

    public function test_produit_interne_pas_de_contexte_marge(): void
    {
        $produit = Produit::create([
            'designation'  => 'Main d\'oeuvre',
            'unite'        => 'h',
            'prix_unitaire' => 50,
            'taux_tva'     => 21,
            'actif'        => true,
        ]);

        $client = $this->createClient();
        $bdc    = $this->createBdc($client, 'BDC-INT');
        $this->createLigneBdc($bdc, 55.00, null, $produit);

        $result = app(VentesHistoriqueService::class)->historique($produit->id, null, null, $client->id);

        $this->assertCount(1, $result['ventes_ce_client']);
        $this->assertNull($result['ventes_ce_client'][0]['contexte_marge']);
    }

    public function test_segmentation_ventes_ce_client_vs_autres(): void
    {
        $cp = CatalogProduit::create([
            'fournisseur' => 'desco', 'reference' => 'SEG-001',
            'designation' => 'Câble', 'prix_catalogue' => 10, 'prix_revente' => 12, 'taux_tva' => 21,
        ]);

        $client1 = $this->createClient('Client A');
        $client2 = $this->createClient('Client B');

        $bdc1 = $this->createBdc($client1, 'BDC-A1');
        $this->createLigneBdc($bdc1, 12.00, $cp);

        $bdc2 = $this->createBdc($client2, 'BDC-B1');
        $this->createLigneBdc($bdc2, 11.50, $cp);

        $result = app(VentesHistoriqueService::class)->historique(null, $cp->id, null, $client1->id);

        $this->assertCount(1, $result['ventes_ce_client']);
        $this->assertEquals($client1->id, $result['ventes_ce_client'][0]['client_id']);

        $this->assertCount(1, $result['ventes_autres_clients']);
        $this->assertEquals($client2->id, $result['ventes_autres_clients'][0]['client_id']);
    }

    public function test_devis_ne_sont_pas_inclus(): void
    {
        $cp = CatalogProduit::create([
            'fournisseur' => 'desco', 'reference' => 'DEVIS-TEST',
            'designation' => 'Test', 'prix_catalogue' => 20, 'prix_revente' => 24, 'taux_tva' => 21,
        ]);

        $client = $this->createClient();

        // Créer une ligne documentable_type = Devis — doit être ignorée
        LigneDocument::create([
            'documentable_type'  => \App\Models\Devis::class,
            'documentable_id'    => 999,
            'catalog_produit_id' => $cp->id,
            'ordre'              => 1,
            'designation'        => 'Test',
            'unite'              => 'pc',
            'quantite'           => 1,
            'prix_unitaire'      => 25.00,
            'taux_tva'           => 21,
            'montant_ht'         => 25.00,
        ]);

        $result = app(VentesHistoriqueService::class)->historique(null, $cp->id, null, $client->id);

        $this->assertCount(0, $result['ventes_ce_client']);
        $this->assertCount(0, $result['ventes_autres_clients']);
    }

    public function test_documents_archives_exclus(): void
    {
        $cp = CatalogProduit::create([
            'fournisseur' => 'desco', 'reference' => 'ARCH-TEST',
            'designation' => 'Archivé', 'prix_catalogue' => 30, 'prix_revente' => 36, 'taux_tva' => 21,
        ]);

        $client = $this->createClient();
        $bdc = BonCommande::create([
            'numero'        => 'BDC-ARCH',
            'client_id'     => $client->id,
            'statut'        => 'archive',
            'date_document' => now()->subMonths(2)->toDateString(),
        ]);
        $this->createLigneBdc($bdc, 35.00, $cp);

        $result = app(VentesHistoriqueService::class)->historique(null, $cp->id, null, $client->id);

        $this->assertCount(0, $result['ventes_ce_client']);
    }

    public function test_periode_24_mois(): void
    {
        $cp = CatalogProduit::create([
            'fournisseur' => 'desco', 'reference' => 'OLD-TEST',
            'designation' => 'Vieux produit', 'prix_catalogue' => 40, 'prix_revente' => 48, 'taux_tva' => 21,
        ]);

        $client = $this->createClient();
        $bdc = BonCommande::create([
            'numero'        => 'BDC-OLD',
            'client_id'     => $client->id,
            'statut'        => 'valide',
            'date_document' => now()->subMonths(25)->toDateString(),
        ]);

        // Forcer la created_at de la ligne hors de la période (created_at n'est pas fillable)
        $ligne = LigneDocument::create([
            'documentable_type'  => BonCommande::class,
            'documentable_id'    => $bdc->id,
            'catalog_produit_id' => $cp->id,
            'ordre'              => 1,
            'designation'        => 'Vieux produit',
            'unite'              => 'pc',
            'quantite'           => 1,
            'prix_unitaire'      => 45.00,
            'taux_tva'           => 21,
            'montant_ht'         => 45.00,
        ]);
        \Illuminate\Support\Facades\DB::table('lignes_document')
            ->where('id', $ligne->id)
            ->update(['created_at' => now()->subMonths(25), 'updated_at' => now()->subMonths(25)]);

        $result = app(VentesHistoriqueService::class)->historique(null, $cp->id, null, $client->id);

        $this->assertCount(0, $result['ventes_ce_client']);
    }
}
