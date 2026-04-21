<?php

namespace Tests\Unit\Services\Catalog\Volatilite;

use App\Models\CatalogPrixHistorique;
use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\BadgeVolatiliteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeVolatiliteServiceTest extends TestCase
{
    use RefreshDatabase;

    private BadgeVolatiliteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BadgeVolatiliteService::class);
        ParametresEntreprise::firstOrCreate(['id' => 1], [
            'nom'                        => 'Test',
            'volatilite_active'          => true,
            'volatilite_seuil_ligne_devis_eur' => 200.0,
        ]);
    }

    public function test_classe_stable_retourne_badge_invisible(): void
    {
        $produit = $this->creerProduit('stable');

        $badge = $this->service->composer($produit);

        $this->assertFalse($badge->visible());
        $this->assertNull($badge->niveau);
        $this->assertNull($badge->message);
    }

    public function test_classe_insuffisant_retourne_badge_invisible(): void
    {
        $produit = $this->creerProduit('insuffisant');

        $badge = $this->service->composer($produit);

        $this->assertFalse($badge->visible());
    }

    public function test_classe_null_retourne_badge_invisible(): void
    {
        $produit = $this->creerProduit(null);

        $badge = $this->service->composer($produit);

        $this->assertFalse($badge->visible());
    }

    public function test_classe_b_hausse_retourne_niveau_warning(): void
    {
        $produit = $this->creerProduit('b', tendancePct: 12.5);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->visible());
        $this->assertSame('warning', $badge->niveau);
        $this->assertSame('📈', $badge->icone);
        $this->assertStringContainsString('+13%', $badge->message);
        $this->assertStringContainsString('hausse régulière', $badge->message);
    }

    public function test_classe_b_baisse_retourne_niveau_opportunite(): void
    {
        $produit = $this->creerProduit('b', tendancePct: -8.0);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->visible());
        $this->assertSame('opportunite', $badge->niveau);
        $this->assertSame('📉', $badge->icone);
        $this->assertStringContainsString('-8%', $badge->message);
        $this->assertStringContainsString('baisse régulière', $badge->message);
    }

    public function test_classe_c_position_basse_retourne_opportunite(): void
    {
        $produit = $this->creerProduit('c', positionRelative: 0.20);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->visible());
        $this->assertSame('opportunite', $badge->niveau);
        $this->assertSame('🎢', $badge->icone);
        $this->assertStringContainsString('bas', $badge->message);
    }

    public function test_classe_c_position_haute_retourne_warning(): void
    {
        $produit = $this->creerProduit('c', positionRelative: 0.80);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->visible());
        $this->assertSame('warning', $badge->niveau);
        $this->assertSame('🎢', $badge->icone);
        $this->assertStringContainsString('haut', $badge->message);
    }

    public function test_classe_c_position_moyenne_retourne_info(): void
    {
        $produit = $this->creerProduit('c', positionRelative: 0.50);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->visible());
        $this->assertSame('info', $badge->niveau);
    }

    public function test_classe_a_retourne_message_avec_variation_historique(): void
    {
        $produit = $this->creerProduit('a');
        CatalogPrixHistorique::create([
            'catalog_produit_id' => $produit->id,
            'fournisseur'        => 'vanmarke',
            'reference'          => 'TEST-A',
            'prix_avant'         => 10.0,
            'prix_apres'         => 11.2,
            'variation_pct'      => 12.0,
            'est_significatif'   => true,
            'source'             => 'csv',
            'detected_at'        => now()->subWeeks(2),
        ]);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->visible());
        $this->assertSame('warning', $badge->niveau);
        $this->assertSame('⚡', $badge->icone);
        $this->assertStringContainsString('Hausse', $badge->message);
        $this->assertStringContainsString('12,0%', $badge->message);
    }

    public function test_signal_fort_vrai_si_signal_absolu(): void
    {
        $produit = $this->creerProduit('b', signalAbsolu: true);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->signalFort);
    }

    public function test_signal_fort_vrai_si_signal_relatif(): void
    {
        $produit = $this->creerProduit('c', signalRelatif: true);

        $badge = $this->service->composer($produit);

        $this->assertTrue($badge->signalFort);
    }

    public function test_signal_fort_faux_si_aucun_signal(): void
    {
        $produit = $this->creerProduit('b', tendancePct: 5.0, signalRelatif: false, signalAbsolu: false);

        $badge = $this->service->composer($produit);

        $this->assertFalse($badge->signalFort);
    }

    public function test_to_array_contient_toutes_les_cles(): void
    {
        $produit = $this->creerProduit('b', tendancePct: 5.0);

        $arr = $this->service->composer($produit)->toArray();

        $this->assertArrayHasKey('classe', $arr);
        $this->assertArrayHasKey('niveau', $arr);
        $this->assertArrayHasKey('icone', $arr);
        $this->assertArrayHasKey('message', $arr);
        $this->assertArrayHasKey('signal_fort', $arr);
    }

    public function test_pertinent_pour_ligne_false_si_module_inactif(): void
    {
        ParametresEntreprise::instance()->update(['volatilite_active' => false]);
        $produit = $this->creerProduit('b', signalAbsolu: true);

        $this->assertFalse($this->service->pertinentPourLigne($produit, 500.0));
    }

    public function test_pertinent_pour_ligne_false_si_montant_sous_seuil(): void
    {
        $produit = $this->creerProduit('b', signalAbsolu: true);

        $this->assertFalse($this->service->pertinentPourLigne($produit, 50.0));
    }

    public function test_pertinent_pour_ligne_true_si_classe_volatile_et_montant_ok(): void
    {
        $produit = $this->creerProduit('b');

        $this->assertTrue($this->service->pertinentPourLigne($produit, 300.0));
    }

    public function test_pertinent_pour_ligne_true_meme_sans_signal_fort(): void
    {
        $produit = $this->creerProduit('c', signalRelatif: false, signalAbsolu: false);

        $this->assertTrue($this->service->pertinentPourLigne($produit, 300.0));
    }

    public function test_pertinent_pour_ligne_false_si_stable(): void
    {
        $produit = $this->creerProduit('stable', signalAbsolu: true);

        $this->assertFalse($this->service->pertinentPourLigne($produit, 500.0));
    }

    // ── Helper ──

    private function creerProduit(
        ?string $classe,
        ?float  $tendancePct      = 5.0,
        ?float  $amplitudePct     = 10.0,
        bool    $signalRelatif    = false,
        bool    $signalAbsolu     = false,
        ?float  $positionRelative = null,
    ): CatalogProduit {
        return CatalogProduit::create([
            'fournisseur'                    => 'vanmarke',
            'reference'                      => 'BADGE-' . uniqid(),
            'designation'                    => 'Test badge',
            'prix_catalogue'                 => 20.00,
            'prix_revente'                   => 20.00,
            'taux_tva'                       => 21,
            'unite'                          => 'pièce',
            'volatilite_classe'              => $classe,
            'volatilite_tendance_pct'        => $tendancePct,
            'volatilite_amplitude_pct'       => $amplitudePct,
            'volatilite_signal_relatif'      => $signalRelatif,
            'volatilite_signal_absolu'       => $signalAbsolu,
            'volatilite_position_relative'   => $positionRelative,
            'volatilite_calculee_at'         => now(),
        ]);
    }
}
