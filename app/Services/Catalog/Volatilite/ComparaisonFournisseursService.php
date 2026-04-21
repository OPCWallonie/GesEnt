<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\DTO\AlternativeFournisseurDTO;
use Illuminate\Support\Collection;

class ComparaisonFournisseursService
{
    public function __construct(private VolatiliteService $volatilite) {}

    public function alternativesAvantageuses(CatalogProduit $produit): Collection
    {
        return $this->toutesAlternatives($produit)
            ->filter(fn(AlternativeFournisseurDTO $a) => $a->nbSignaux() > 0)
            ->values();
    }

    public function toutesAlternatives(CatalogProduit $produit): Collection
    {
        if (! $produit->ean) {
            return collect();
        }

        $params     = ParametresEntreprise::instance();
        $seuil_prix = (float) $params->volatilite_cross_seuil_prix_pct;
        $seuil_pos  = (float) $params->volatilite_cross_seuil_position;
        $seuil_tend = (float) $params->volatilite_cross_seuil_tendance_pp;

        $refPrix     = (float) $produit->prix_catalogue;
        $refPosition = $produit->volatilite_position_relative !== null
            ? (float) $produit->volatilite_position_relative
            : null;
        $refTendance = $produit->volatilite_tendance_pct !== null
            ? (float) $produit->volatilite_tendance_pct
            : null;

        $candidates = $this->volatilite->compareurEan($produit->ean)
            ->filter(fn(CatalogProduit $p) => $p->id !== $produit->id);

        return $candidates
            ->map(function (CatalogProduit $alt) use ($refPrix, $refPosition, $refTendance, $seuil_prix, $seuil_pos, $seuil_tend) {
                return $this->construireAlternative($alt, $refPrix, $refPosition, $refTendance, $seuil_prix, $seuil_pos, $seuil_tend);
            })
            ->sortBy('scoreComposite')
            ->values();
    }

    private function construireAlternative(
        CatalogProduit $alt,
        float  $refPrix,
        ?float $refPosition,
        ?float $refTendance,
        float  $seuilPrix,
        float  $seuilPos,
        float  $seuilTend,
    ): AlternativeFournisseurDTO {
        $altPrix     = (float) $alt->prix_catalogue;
        $altPosition = $alt->volatilite_position_relative !== null ? (float) $alt->volatilite_position_relative : null;
        $altTendance = $alt->volatilite_tendance_pct !== null ? (float) $alt->volatilite_tendance_pct : null;

        $ecartPrixPct = $refPrix > 0 ? (($altPrix - $refPrix) / $refPrix) * 100 : 0.0;

        $signalPrix     = $refPrix > 0 && $altPrix < $refPrix * (1 - $seuilPrix / 100);
        $signalPosition = $refPosition !== null && $altPosition !== null
            && $altPosition < ($refPosition - $seuilPos);
        $signalTendance = $refTendance !== null && $altTendance !== null
            && $altTendance < ($refTendance - $seuilTend);

        // Score composite : négatif = meilleure alternative
        $diffPosition = ($refPosition !== null && $altPosition !== null)
            ? ($altPosition - $refPosition) * 100
            : 0.0;
        $diffTendance = ($refTendance !== null && $altTendance !== null)
            ? ($altTendance - $refTendance)
            : 0.0;

        $scoreComposite = 0.5 * $ecartPrixPct + 0.3 * $diffPosition + 0.2 * $diffTendance;

        return new AlternativeFournisseurDTO(
            produit:                   $alt,
            ecartPrixPct:              $ecartPrixPct,
            positionRelative:          $altPosition,
            tendance12mPct:            $altTendance,
            scoreComposite:            $scoreComposite,
            signalPrixInferieur:       $signalPrix,
            signalPositionInferieure:  $signalPosition,
            signalTendanceFavorable:   $signalTendance,
        );
    }
}
