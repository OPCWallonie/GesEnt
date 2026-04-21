<?php

namespace App\Services\Catalog\Volatilite\DTO;

use App\Models\CatalogProduit;

final class AlternativeFournisseurDTO
{
    public function __construct(
        public readonly CatalogProduit $produit,
        public readonly float          $ecartPrixPct,
        public readonly ?float         $positionRelative,
        public readonly ?float         $tendance12mPct,
        public readonly float          $scoreComposite,
        public readonly bool           $signalPrixInferieur,
        public readonly bool           $signalPositionInferieure,
        public readonly bool           $signalTendanceFavorable,
    ) {}

    public function nbSignaux(): int
    {
        return (int) $this->signalPrixInferieur
             + (int) $this->signalPositionInferieure
             + (int) $this->signalTendanceFavorable;
    }

    public function toArray(): array
    {
        return [
            'produit_id'               => $this->produit->id,
            'fournisseur'              => $this->produit->fournisseur,
            'nom_fournisseur'          => $this->produit->nom_fournisseur ?? $this->produit->fournisseur,
            'reference'                => $this->produit->reference,
            'designation'              => $this->produit->designation,
            'prix_catalogue'           => (float) $this->produit->prix_catalogue,
            'prix_revente'             => (float) $this->produit->prix_revente,
            'taux_tva'                 => (float) $this->produit->taux_tva,
            'en_stock'                 => (bool) $this->produit->en_stock,
            'ecart_prix_pct'           => round($this->ecartPrixPct, 2),
            'position_relative'        => $this->positionRelative !== null ? round($this->positionRelative, 4) : null,
            'tendance_12m_pct'         => $this->tendance12mPct !== null ? round($this->tendance12mPct, 2) : null,
            'score_composite'          => round($this->scoreComposite, 4),
            'signal_prix_inferieur'    => $this->signalPrixInferieur,
            'signal_position_inferieure' => $this->signalPositionInferieure,
            'signal_tendance_favorable'  => $this->signalTendanceFavorable,
            'nb_signaux'               => $this->nbSignaux(),
        ];
    }
}
