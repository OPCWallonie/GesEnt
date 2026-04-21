<?php

namespace Tests\Unit\Services\Catalog\Volatilite;

use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\ClassificateurVolatilite;
use App\Services\Catalog\Volatilite\DTO\IndicateursDTO;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClassificateurVolatiliteTest extends TestCase
{
    private ClassificateurVolatilite $classificateur;
    private ParametresEntreprise $params;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classificateur = new ClassificateurVolatilite();
        $this->params         = $this->makeParams();
    }

    public function test_classe_insuffisant_quand_nb_changements_sous_seuil(): void
    {
        $dto    = $this->makeIndicateurs(nbChangements: 2);
        $result = $this->classificateur->classifier($dto, 0.0, 'fournisseur', $this->params);
        $this->assertEquals('insuffisant', $result->classe);
    }

    public function test_classe_stable_quand_amplitude_sous_seuil(): void
    {
        $dto    = $this->makeIndicateurs(nbChangements: 5, amplitudePct: 1.5, tendance12mPct: 3.0);
        $result = $this->classificateur->classifier($dto, 0.0, 'fournisseur', $this->params);
        $this->assertEquals('stable', $result->classe);
    }

    public function test_classe_a_anomalie_isolee_recente(): void
    {
        $dto = $this->makeIndicateurs(
            nbChangements: 4,
            amplitudePct: 15.0,
            tendance12mPct: 13.5,
            variationsRecentes3m: [['date' => Carbon::now()->subWeeks(2), 'pct' => 13.5]],
            nbChangementsAnciens: 3,
        );
        $result = $this->classificateur->classifier($dto, 0.0, 'fournisseur', $this->params);
        $this->assertEquals('a', $result->classe);
    }

    public function test_classe_b_augmentation_constante(): void
    {
        $dto = $this->makeIndicateurs(
            nbChangements: 5,
            amplitudePct: 50.0,
            tendance12mPct: 70.0,
            r2Tendance: 0.95,
            variationsRecentes3m: [['date' => Carbon::now()->subWeeks(4), 'pct' => 5.0]],
            nbChangementsAnciens: 8,
        );
        $result = $this->classificateur->classifier($dto, 0.0, 'fournisseur', $this->params);
        $this->assertEquals('b', $result->classe);
    }

    public function test_classe_b_necessite_r2_suffisant(): void
    {
        $dto = $this->makeIndicateurs(
            nbChangements: 5,
            amplitudePct: 30.0,
            tendance12mPct: 20.0,
            r2Tendance: 0.4,  // trop faible
        );
        $result = $this->classificateur->classifier($dto, 0.0, 'fournisseur', $this->params);
        $this->assertNotEquals('b', $result->classe);
    }

    public function test_classe_c_yoyo(): void
    {
        $dto = $this->makeIndicateurs(
            nbChangements: 8,
            amplitudePct: 25.0,
            tendance12mPct: 3.0,
            r2Tendance: 0.1,
        );
        $result = $this->classificateur->classifier($dto, 0.0, 'fournisseur', $this->params);
        $this->assertEquals('c', $result->classe);
    }

    public function test_signal_absolu_active_si_tendance_depasse_garde_fou(): void
    {
        $dto = $this->makeIndicateurs(nbChangements: 5, amplitudePct: 3.0, tendance12mPct: 20.0);
        $result = $this->classificateur->classifier($dto, 18.0, 'fournisseur', $this->params);
        $this->assertTrue($result->signalAbsolu);
    }

    public function test_signal_relatif_active_si_ecart_vs_mediane_depasse_seuil(): void
    {
        $dto = $this->makeIndicateurs(nbChangements: 5, amplitudePct: 3.0, tendance12mPct: 15.0);
        $result = $this->classificateur->classifier($dto, 8.0, 'fournisseur', $this->params);
        // écart = |15 - 8| = 7 >= seuil 5 → signal relatif true
        $this->assertTrue($result->signalRelatif);
    }

    public function test_signal_relatif_false_si_ecart_sous_seuil(): void
    {
        $dto = $this->makeIndicateurs(nbChangements: 5, amplitudePct: 3.0, tendance12mPct: 12.0);
        $result = $this->classificateur->classifier($dto, 11.0, 'fournisseur', $this->params);
        // écart = |12 - 11| = 1 < seuil 5 → signal relatif false
        $this->assertFalse($result->signalRelatif);
    }

    public function test_signaux_false_si_tendance_null(): void
    {
        $dto = $this->makeIndicateurs(nbChangements: 5, amplitudePct: 3.0, tendance12mPct: null);
        $result = $this->classificateur->classifier($dto, null, 'fournisseur', $this->params);
        $this->assertFalse($result->signalAbsolu);
        $this->assertFalse($result->signalRelatif);
    }

    public function test_groupe_comparaison_est_persiste(): void
    {
        $dto    = $this->makeIndicateurs(nbChangements: 5, amplitudePct: 1.0, tendance12mPct: 2.0);
        $result = $this->classificateur->classifier($dto, 0.0, 'sous_categorie', $this->params);
        $this->assertEquals('sous_categorie', $result->groupeComparaison);
    }

    // ── Helpers ──

    private function makeIndicateurs(
        int $nbChangements = 5,
        ?float $amplitudePct = 3.0,
        ?float $tendance12mPct = 5.0,
        ?float $r2Tendance = null,
        array $variationsRecentes3m = [],
        int $nbChangementsAnciens = 5,
        ?float $prixMin = 100.0,
        ?float $prixMax = 103.0,
        ?float $prixMoyen = 101.5,
        ?float $positionRelative = 1.0,
    ): IndicateursDTO {
        return new IndicateursDTO(
            nbChangements:       $nbChangements,
            prixMin:             $prixMin,
            prixMax:             $prixMax,
            prixMoyen:           $prixMoyen,
            amplitudePct:        $amplitudePct,
            positionRelative:    $positionRelative,
            tendance12mPct:      $tendance12mPct,
            r2Tendance:          $r2Tendance,
            variationsRecentes3m: $variationsRecentes3m,
            nbChangementsAnciens: $nbChangementsAnciens,
        );
    }

    private function makeParams(): ParametresEntreprise
    {
        $p = new ParametresEntreprise();
        $p->volatilite_active                         = true;
        $p->volatilite_fenetre_mois                   = 24;
        $p->volatilite_min_changements_pour_classer   = 3;
        $p->volatilite_seuil_stable_amplitude_pct     = 2.00;
        $p->volatilite_seuil_a_variation_pct          = 8.00;
        $p->volatilite_seuil_a_max_changements_anciens= 3;
        $p->volatilite_seuil_b_pente_annuelle_pct     = 10.00;
        $p->volatilite_seuil_b_r2_min                 = 0.700;
        $p->volatilite_seuil_c_nb_changements         = 4;
        $p->volatilite_seuil_c_amplitude_pct          = 10.00;
        $p->volatilite_garde_fou_absolu_pct            = 15.00;
        $p->volatilite_signal_relatif_ecart_pct        = 5.00;
        return $p;
    }
}
