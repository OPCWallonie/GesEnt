<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\CatalogPrixHistorique;
use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\DTO\VolatiliteBadgeDTO;
use Illuminate\Support\Carbon;

class BadgeVolatiliteService
{
    public function composer(CatalogProduit $produit): VolatiliteBadgeDTO
    {
        $classe = $produit->volatilite_classe;

        if (!$classe || $classe === 'insuffisant' || $classe === 'stable') {
            return new VolatiliteBadgeDTO(
                classe:      $classe ?? 'insuffisant',
                niveau:      null,
                icone:       null,
                message:     null,
                signalFort:  false,
            );
        }

        $signalFort = (bool) $produit->volatilite_signal_absolu || (bool) $produit->volatilite_signal_relatif;

        return match ($classe) {
            'a'     => $this->badgeClasseA($produit, $signalFort),
            'b'     => $this->badgeClasseB($produit, $signalFort),
            'c'     => $this->badgeClasseC($produit, $signalFort),
            default => new VolatiliteBadgeDTO($classe, null, null, null, false),
        };
    }

    public function pertinentPourLigne(CatalogProduit $produit, float $montantLigne): bool
    {
        $params = ParametresEntreprise::instance();

        if (! $params->volatilite_active) {
            return false;
        }

        $classe = $produit->volatilite_classe;
        if (!$classe || $classe === 'insuffisant' || $classe === 'stable') {
            return false;
        }

        if ($montantLigne < (float) $params->volatilite_seuil_ligne_devis_eur) {
            return false;
        }

        return (bool) $produit->volatilite_signal_absolu || (bool) $produit->volatilite_signal_relatif;
    }

    private function badgeClasseA(CatalogProduit $produit, bool $signalFort): VolatiliteBadgeDTO
    {
        // Requête la variation la plus forte dans les 3 derniers mois
        $variationMax = CatalogPrixHistorique::where('catalog_produit_id', $produit->id)
            ->where('detected_at', '>=', now()->subMonths(3))
            ->orderByRaw('ABS(variation_pct) DESC')
            ->value('variation_pct');

        if ($variationMax !== null) {
            $signe   = (float) $variationMax >= 0 ? '+' : '';
            $message = "Variation ponctuelle : {$signe}" . number_format((float) $variationMax, 1) . '%';
        } else {
            $message = 'Variation ponctuelle détectée';
        }

        return new VolatiliteBadgeDTO(
            classe:     'a',
            niveau:     'warning',
            icone:      '⚠️',
            message:    $message,
            signalFort: $signalFort,
        );
    }

    private function badgeClasseB(CatalogProduit $produit, bool $signalFort): VolatiliteBadgeDTO
    {
        $tendance = (float) ($produit->volatilite_tendance_pct ?? 0);
        $hausse   = $tendance >= 0;
        $signe    = $hausse ? '+' : '';

        return new VolatiliteBadgeDTO(
            classe:     'b',
            niveau:     $hausse ? 'warning' : 'opportunite',
            icone:      $hausse ? '📈' : '📉',
            message:    ($hausse ? 'Hausse continue' : 'Baisse en cours')
                        . ' : ' . $signe . number_format($tendance, 1) . '% sur 12 mois',
            signalFort: $signalFort,
        );
    }

    private function badgeClasseC(CatalogProduit $produit, bool $signalFort): VolatiliteBadgeDTO
    {
        $amplitude = $produit->volatilite_amplitude_pct !== null
            ? number_format((float) $produit->volatilite_amplitude_pct, 1)
            : '?';

        return new VolatiliteBadgeDTO(
            classe:     'c',
            niveau:     'warning',
            icone:      '🔄',
            message:    "Prix instable · amplitude {$amplitude}%",
            signalFort: $signalFort,
        );
    }
}
