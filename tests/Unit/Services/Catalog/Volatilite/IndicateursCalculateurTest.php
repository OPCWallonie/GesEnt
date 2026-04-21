<?php

namespace Tests\Unit\Services\Catalog\Volatilite;

use App\Models\CatalogPrixHistorique;
use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\IndicateursCalculateur;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class IndicateursCalculateurTest extends TestCase
{
    private IndicateursCalculateur $calculateur;
    private ParametresEntreprise $params;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculateur = new IndicateursCalculateur();
        $this->params      = $this->makeParams();
    }

    public function test_retourne_dto_vide_si_historique_vide(): void
    {
        $produit = $this->makeProduit(10.00);
        $dto     = $this->calculateur->calculer($produit, collect(), $this->params);

        $this->assertEquals(0, $dto->nbChangements);
        $this->assertNull($dto->prixMin);
        $this->assertNull($dto->amplitudePct);
        $this->assertFalse($dto->suffisant(3));
    }

    public function test_retourne_nb_changements_correct(): void
    {
        $produit    = $this->makeProduit(110.00);
        $historique = $this->makeHistorique([
            ['mois' => 10, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  6, 'prix_apres' => 105.0, 'pct' => 5.0],
            ['mois' =>  3, 'prix_apres' => 110.0, 'pct' => 4.76],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertEquals(3, $dto->nbChangements);
        $this->assertTrue($dto->suffisant(3));
    }

    public function test_calcul_amplitude(): void
    {
        // prix entre 100 et 130 → amplitude = (130-100)/115 * 100 ≈ 26.09%
        $produit    = $this->makeProduit(130.00);
        $historique = $this->makeHistorique([
            ['mois' => 20, 'prix_apres' => 100.0, 'pct' =>   0.0],
            ['mois' => 12, 'prix_apres' => 115.0, 'pct' =>  15.0],
            ['mois' =>  4, 'prix_apres' => 130.0, 'pct' =>  13.0],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertEqualsWithDelta(26.09, $dto->amplitudePct, 0.5);
    }

    public function test_position_relative_zero_au_plus_bas(): void
    {
        // Prix actuel = min de l'historique → position = 0
        $produit    = $this->makeProduit(100.0);
        $historique = $this->makeHistorique([
            ['mois' => 10, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  6, 'prix_apres' => 120.0, 'pct' => 20.0],
            ['mois' =>  3, 'prix_apres' => 130.0, 'pct' => 8.33],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertEqualsWithDelta(0.0, $dto->positionRelative, 0.01);
    }

    public function test_position_relative_un_au_plus_haut(): void
    {
        $produit    = $this->makeProduit(130.0);
        $historique = $this->makeHistorique([
            ['mois' => 10, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  6, 'prix_apres' => 115.0, 'pct' => 15.0],
            ['mois' =>  3, 'prix_apres' => 130.0, 'pct' => 13.0],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertEqualsWithDelta(1.0, $dto->positionRelative, 0.01);
    }

    public function test_position_relative_clamped_si_hors_fourchette(): void
    {
        // Prix actuel supérieur au max historique → doit rester à 1.0
        $produit    = $this->makeProduit(200.0);
        $historique = $this->makeHistorique([
            ['mois' => 10, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  5, 'prix_apres' => 130.0, 'pct' => 30.0],
            ['mois' =>  2, 'prix_apres' => 120.0, 'pct' => -7.7],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertLessThanOrEqual(1.0, $dto->positionRelative);
        $this->assertGreaterThanOrEqual(0.0, $dto->positionRelative);
    }

    public function test_prix_min_egal_max_donne_position_05(): void
    {
        $produit    = $this->makeProduit(100.0);
        $historique = $this->makeHistorique([
            ['mois' => 10, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  6, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  3, 'prix_apres' => 100.0, 'pct' => 0.0],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertEqualsWithDelta(0.5, $dto->positionRelative, 0.01);
        $this->assertEqualsWithDelta(0.0, $dto->amplitudePct, 0.01);
    }

    public function test_tendance_12m_calculee_depuis_prix_avant_12m(): void
    {
        // Prix il y a 14 mois = 100, prix actuel = 115 → tendance ≈ +15%
        $produit = $this->makeProduit(115.0);
        $historique = $this->makeHistorique([
            ['mois' => 14, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  6, 'prix_apres' => 108.0, 'pct' => 8.0],
            ['mois' =>  2, 'prix_apres' => 115.0, 'pct' => 6.5],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertNotNull($dto->tendance12mPct);
        $this->assertEqualsWithDelta(15.0, $dto->tendance12mPct, 1.0);
    }

    public function test_r2_proche_de_1_sur_serie_lineaire(): void
    {
        // Série linéaire parfaite sur 12 mois → R² doit être très proche de 1
        $produit    = $this->makeProduit(130.0);
        $historique = $this->makeHistorique([
            ['mois' => 13, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' => 12, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  9, 'prix_apres' => 107.5, 'pct' => 7.5],
            ['mois' =>  6, 'prix_apres' => 115.0, 'pct' => 7.0],
            ['mois' =>  3, 'prix_apres' => 122.5, 'pct' => 6.5],
            ['mois' =>  1, 'prix_apres' => 128.0, 'pct' => 4.5],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertNotNull($dto->r2Tendance);
        $this->assertGreaterThan(0.95, $dto->r2Tendance);
    }

    public function test_r2_null_si_moins_de_3_points_sur_12m(): void
    {
        $produit    = $this->makeProduit(110.0);
        $historique = $this->makeHistorique([
            ['mois' => 14, 'prix_apres' => 100.0, 'pct' => 0.0],
            ['mois' =>  8, 'prix_apres' => 105.0, 'pct' => 5.0],
            ['mois' =>  2, 'prix_apres' => 110.0, 'pct' => 4.8],
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        // Seulement 2 points dans les 12 derniers mois (8 mois et 2 mois)
        $this->assertNull($dto->r2Tendance);
    }

    public function test_variations_recentes_3m_filtrees_correctement(): void
    {
        $produit    = $this->makeProduit(115.0);
        $historique = $this->makeHistorique([
            ['mois' => 20, 'prix_apres' => 100.0, 'pct' =>  5.0],
            ['mois' =>  6, 'prix_apres' => 108.0, 'pct' =>  8.0],  // ancien
            ['mois' =>  2, 'prix_apres' => 112.0, 'pct' =>  3.7],  // récent
            ['mois' =>  1, 'prix_apres' => 115.0, 'pct' =>  2.7],  // récent
        ]);

        $dto = $this->calculateur->calculer($produit, $historique, $this->params);
        $this->assertCount(2, $dto->variationsRecentes3m);
    }

    // ── Helpers ──

    private function makeProduit(float $prix): CatalogProduit
    {
        $p                 = new CatalogProduit();
        $p->id             = 999;
        $p->fournisseur    = 'test';
        $p->reference      = 'TEST-001';
        $p->designation    = 'Produit test';
        $p->prix_catalogue = $prix;
        $p->volatilite_flag_manuel = 'auto';
        return $p;
    }

    private function makeHistorique(array $data): Collection
    {
        return collect($data)->map(function ($d) {
            $h                   = new CatalogPrixHistorique();
            $h->prix_apres       = $d['prix_apres'];
            $h->prix_avant       = $d['prix_apres'] / (1 + $d['pct'] / 100);
            $h->variation_pct    = $d['pct'];
            $h->detected_at      = now()->subMonths($d['mois']);
            $h->est_significatif = abs($d['pct']) >= 3;
            return $h;
        });
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
