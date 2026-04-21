<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\DTO\IndicateursDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class IndicateursCalculateur
{
    public function calculer(CatalogProduit $produit, Collection $historique, ParametresEntreprise $params): IndicateursDTO
    {
        $fenetre = (int) $params->volatilite_fenetre_mois;
        $debut   = now()->subMonths($fenetre);

        $dansLaFenetre = $historique
            ->filter(fn($h) => Carbon::parse($h->detected_at)->gte($debut))
            ->sortBy('detected_at')
            ->values();

        if ($dansLaFenetre->isEmpty()) {
            return new IndicateursDTO(0, null, null, null, null, null, null, null, [], 0);
        }

        $nbChangements = $dansLaFenetre->count();
        $prix          = $dansLaFenetre->pluck('prix_apres')->map(fn($p) => (float) $p);

        $prixMin   = $prix->min();
        $prixMax   = $prix->max();
        $prixMoyen = $prix->average();

        $amplitudePct = $prixMoyen > 0
            ? (($prixMax - $prixMin) / $prixMoyen) * 100
            : 0.0;

        $prixActuel = (float) $produit->prix_catalogue;
        if ($prixMax !== $prixMin) {
            $positionRelative = ($prixActuel - $prixMin) / ($prixMax - $prixMin);
            $positionRelative = max(0.0, min(1.0, $positionRelative));
        } else {
            $positionRelative = 0.5;
        }

        $tendance12mPct = $this->calculerTendance12m($produit, $historique, $dansLaFenetre);

        $il_y_a_12m = now()->subMonths(12);
        $depuis12m  = $dansLaFenetre->filter(fn($h) => Carbon::parse($h->detected_at)->gte($il_y_a_12m));
        $r2Tendance = $depuis12m->count() >= 3
            ? $this->calculerR2($depuis12m)
            : null;

        $il_y_a_3m = now()->subMonths(3);
        $variationsRecentes3m = $dansLaFenetre
            ->filter(fn($h) => Carbon::parse($h->detected_at)->gte($il_y_a_3m))
            ->map(fn($h) => [
                'date' => Carbon::parse($h->detected_at),
                'pct'  => (float) $h->variation_pct,
            ])
            ->values()
            ->all();

        $nbChangementsAnciens = $dansLaFenetre
            ->filter(fn($h) => Carbon::parse($h->detected_at)->lt($il_y_a_3m))
            ->count();

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

    private function calculerTendance12m(CatalogProduit $produit, Collection $toutHistorique, Collection $dansLaFenetre): ?float
    {
        $il_y_a_12m = now()->subMonths(12);

        // Dernier prix enregistré avant il y a 12 mois
        $ancienPrix = $toutHistorique
            ->filter(fn($h) => Carbon::parse($h->detected_at)->lte($il_y_a_12m))
            ->sortByDesc('detected_at')
            ->first();

        if ($ancienPrix) {
            $prixDeRef = (float) $ancienPrix->prix_apres;
        } else {
            // Fallback : premier prix_avant enregistré dans la fenêtre
            $premier = $dansLaFenetre->first();
            if (!$premier) return null;
            $prixDeRef = (float) $premier->prix_avant;
        }

        if ($prixDeRef <= 0) return null;

        $prixActuel = (float) $produit->prix_catalogue;
        return (($prixActuel - $prixDeRef) / $prixDeRef) * 100;
    }

    private function calculerR2(Collection $historique12m): float
    {
        $debut  = Carbon::parse($historique12m->first()->detected_at);
        $points = $historique12m->map(function ($h) use ($debut) {
            $jours = $debut->diffInDays(Carbon::parse($h->detected_at));
            return ['x' => $jours, 'y' => (float) $h->prix_apres];
        })->values();

        $n  = $points->count();
        $xm = $points->avg('x');
        $ym = $points->avg('y');

        $sxy = $points->sum(fn($p) => ($p['x'] - $xm) * ($p['y'] - $ym));
        $sxx = $points->sum(fn($p) => ($p['x'] - $xm) ** 2);

        if ($sxx == 0) return 0.0;

        $b = $sxy / $sxx;
        $a = $ym - $b * $xm;

        $ssTot = $points->sum(fn($p) => ($p['y'] - $ym) ** 2);
        $ssRes = $points->sum(fn($p) => ($p['y'] - ($a + $b * $p['x'])) ** 2);

        if ($ssTot == 0) return 1.0;

        return max(0.0, 1 - $ssRes / $ssTot);
    }
}
