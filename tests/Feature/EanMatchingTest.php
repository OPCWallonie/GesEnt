<?php

namespace Tests\Feature;

use App\Models\CatalogProduit;
use App\Services\Catalog\EanMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EanMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    private function creerProduit(array $attrs = []): CatalogProduit
    {
        return CatalogProduit::create(array_merge([
            'fournisseur'    => 'desco',
            'reference'      => 'REF-' . uniqid(),
            'designation'    => 'Produit test',
            'prix_catalogue' => 100,
            'prix_revente'   => 120,
            'taux_tva'       => 21,
        ], $attrs));
    }

    public function test_equivalents_triés_par_prix_croissant(): void
    {
        $ean = '4005176123456';

        $desco = $this->creerProduit(['fournisseur' => 'desco',    'ean' => $ean, 'prix_catalogue' => 89]);
        $vmk   = $this->creerProduit(['fournisseur' => 'vanmarke', 'ean' => $ean, 'prix_catalogue' => 82]);
        $wasco = $this->creerProduit(['fournisseur' => 'wasco',    'ean' => $ean, 'prix_catalogue' => 95]);

        $equivalents = app(EanMatchingService::class)->equivalentsAutresFournisseurs($desco);

        $this->assertCount(2, $equivalents);
        $this->assertEquals($vmk->id, $equivalents->first()->id);
        $this->assertEqualsWithDelta(82.00, (float) $equivalents->first()->prix_catalogue, 0.001);
        $this->assertEquals($wasco->id, $equivalents->last()->id);
    }

    public function test_produit_sans_ean_retourne_collection_vide(): void
    {
        $p = $this->creerProduit(['ean' => null]);
        $result = app(EanMatchingService::class)->equivalentsAutresFournisseurs($p);
        $this->assertTrue($result->isEmpty());
    }

    public function test_produit_seul_avec_son_ean_retourne_collection_vide(): void
    {
        $p = $this->creerProduit(['ean' => '1234567890123']);
        $result = app(EanMatchingService::class)->equivalentsAutresFournisseurs($p);
        $this->assertTrue($result->isEmpty());
    }

    public function test_nb_fournisseurs_distincts_pour_ean(): void
    {
        $ean = '9999999999999';
        $this->creerProduit(['fournisseur' => 'desco',    'ean' => $ean]);
        $this->creerProduit(['fournisseur' => 'vanmarke', 'ean' => $ean]);
        $this->creerProduit(['fournisseur' => 'desco',    'ean' => $ean, 'reference' => 'AUTRE-DESCO-' . uniqid()]);

        $nb = app(EanMatchingService::class)->nbFournisseursPourEan($ean);
        $this->assertEquals(2, $nb);
    }
}
