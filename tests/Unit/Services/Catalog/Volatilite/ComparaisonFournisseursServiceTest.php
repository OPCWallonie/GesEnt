<?php

namespace Tests\Unit\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\ComparaisonFournisseursService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComparaisonFournisseursServiceTest extends TestCase
{
    use RefreshDatabase;

    private ComparaisonFournisseursService $service;
    private const EAN = '4011200099999';

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ComparaisonFournisseursService::class);
        ParametresEntreprise::firstOrCreate(['id' => 1], [
            'nom'                              => 'Test',
            'volatilite_active'                => true,
            'volatilite_cross_seuil_prix_pct'  => 5.0,
            'volatilite_cross_seuil_position'  => 0.30,
            'volatilite_cross_seuil_tendance_pp'=> 10.0,
        ]);
    }

    public function test_sans_ean_retourne_collection_vide(): void
    {
        $produit = $this->creerProduit('vanmarke', 10.0, ean: null);

        $this->assertCount(0, $this->service->toutesAlternatives($produit));
    }

    public function test_sans_autre_produit_meme_ean_retourne_vide(): void
    {
        $produit = $this->creerProduit('vanmarke', 10.0);

        $this->assertCount(0, $this->service->toutesAlternatives($produit));
    }

    public function test_retourne_alternatives_avec_meme_ean(): void
    {
        $ref = $this->creerProduit('vanmarke', 10.0);
        $alt = $this->creerProduit('wasco', 9.0);

        $resultats = $this->service->toutesAlternatives($ref);

        $this->assertCount(1, $resultats);
        $this->assertSame($alt->id, $resultats->first()->produit->id);
    }

    public function test_signal_prix_inferieur_detecte(): void
    {
        $ref = $this->creerProduit('vanmarke', 10.0);
        $this->creerProduit('wasco', 9.0); // 10% moins cher > seuil 5%

        $alt = $this->service->toutesAlternatives($ref)->first();

        $this->assertTrue($alt->signalPrixInferieur);
    }

    public function test_signal_prix_inferieur_non_detecte_si_ecart_insuffisant(): void
    {
        $ref = $this->creerProduit('vanmarke', 10.0);
        $this->creerProduit('wasco', 9.8); // 2% → sous le seuil de 5%

        $alt = $this->service->toutesAlternatives($ref)->first();

        $this->assertFalse($alt->signalPrixInferieur);
    }

    public function test_signal_tendance_favorable_detecte(): void
    {
        $ref = $this->creerProduit('vanmarke', 10.0, tendance: 15.0);
        $this->creerProduit('wasco', 10.0, tendance: 2.0); // écart de 13pp > seuil 10pp

        $alt = $this->service->toutesAlternatives($ref)->first();

        $this->assertTrue($alt->signalTendanceFavorable);
    }

    public function test_score_composite_negatif_si_moins_cher(): void
    {
        $ref = $this->creerProduit('vanmarke', 10.0);
        $this->creerProduit('wasco', 8.0); // 20% moins cher

        $alt = $this->service->toutesAlternatives($ref)->first();

        $this->assertLessThan(0, $alt->scoreComposite);
        $this->assertLessThan(0, $alt->ecartPrixPct);
    }

    public function test_alternatives_avantageuses_filtre_sans_signaux(): void
    {
        $ref = $this->creerProduit('vanmarke', 10.0);
        $this->creerProduit('wasco', 10.2); // plus cher — aucun signal

        $avantageuses = $this->service->alternativesAvantageuses($ref);

        $this->assertCount(0, $avantageuses);
    }

    public function test_alternatives_triees_score_croissant(): void
    {
        $ref = $this->creerProduit('vanmarke', 10.0);
        $this->creerProduit('wasco', 7.0);   // -30%
        $this->creerProduit('desco', 9.0);   // -10%

        $resultats = $this->service->toutesAlternatives($ref);

        $scores = $resultats->pluck('scoreComposite')->all();
        $this->assertEquals($scores, collect($scores)->sort()->values()->all());
    }

    // ── Helper ──

    private function creerProduit(
        string  $fournisseur,
        float   $prix,
        ?string $ean       = self::EAN,
        ?float  $tendance  = null,
        ?float  $position  = null,
    ): CatalogProduit {
        return CatalogProduit::create([
            'fournisseur'                   => $fournisseur,
            'reference'                     => strtoupper($fournisseur) . '-' . rand(100, 999),
            'designation'                   => "Produit {$fournisseur}",
            'prix_catalogue'                => $prix,
            'prix_revente'                  => $prix,
            'taux_tva'                      => 21,
            'unite'                         => 'pièce',
            'ean'                           => $ean,
            'volatilite_tendance_pct'       => $tendance,
            'volatilite_position_relative'  => $position,
            'volatilite_calculee_at'        => now(),
        ]);
    }
}
