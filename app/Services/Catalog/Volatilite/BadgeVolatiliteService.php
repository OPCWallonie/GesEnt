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
        if (! $params->volatilite_active) return false;
        $classe = $produit->volatilite_classe;
        if (!$classe || $classe === 'insuffisant' || $classe === 'stable') return false;
        return $montantLigne >= (float) $params->volatilite_seuil_ligne_devis_eur;
    }

    private function badgeClasseA(CatalogProduit $produit, bool $signalFort): VolatiliteBadgeDTO
    {
        $plusForte = CatalogPrixHistorique::where('catalog_produit_id', $produit->id)
            ->where('detected_at', '>=', now()->subMonths(3))
            ->orderByRaw('ABS(variation_pct) DESC')
            ->first();

        if ($plusForte !== null) {
            $variation = (float) $plusForte->variation_pct;
            $nMois     = max(1, (int) round(now()->diffInMonths(Carbon::parse($plusForte->detected_at))));
            $mot       = $variation >= 0 ? 'Hausse' : 'Baisse';
            $signe     = $variation >= 0 ? '+' : '';
            $message   = sprintf('⚡ %s récente inhabituelle (%s%s%% il y a %d mois)',
                $mot, $signe, number_format($variation, 1, ',', ''), $nMois);
        } else {
            $message = '⚡ Variation récente inhabituelle';
        }

        return new VolatiliteBadgeDTO(classe: 'a', niveau: 'warning', icone: '⚡', message: $message, signalFort: $signalFort);
    }

    private function badgeClasseB(CatalogProduit $produit, bool $signalFort): VolatiliteBadgeDTO
    {
        $tendance        = (float) ($produit->volatilite_tendance_pct ?? 0);
        $tendanceArrondie = (int) round($tendance);
        $hausse           = $tendance >= 0;

        if ($hausse) {
            $message = sprintf('📈 En hausse régulière (+%d%%/an). Envisager de stocker pour un chantier long.', $tendanceArrondie);
            $niveau  = 'warning';
            $icone   = '📈';
        } else {
            $message = sprintf('📉 En baisse régulière (%d%%/an). Bon moment pour acheter.', $tendanceArrondie);
            $niveau  = 'opportunite';
            $icone   = '📉';
        }

        return new VolatiliteBadgeDTO(classe: 'b', niveau: $niveau, icone: $icone, message: $message, signalFort: $signalFort);
    }

    private function badgeClasseC(CatalogProduit $produit, bool $signalFort): VolatiliteBadgeDTO
    {
        $position = $produit->volatilite_position_relative !== null
            ? (float) $produit->volatilite_position_relative
            : 0.5;

        if ($position < 0.33) {
            $message = '🎢 Prix fluctuant, actuellement bas. Bon timing.';
            $niveau  = 'opportunite';
        } elseif ($position <= 0.66) {
            $message = '🎢 Prix fluctuant, dans la moyenne.';
            $niveau  = 'info';
        } else {
            $message = '🎢 Prix fluctuant, actuellement haut. Attendre si possible.';
            $niveau  = 'warning';
        }

        return new VolatiliteBadgeDTO(classe: 'c', niveau: $niveau, icone: '🎢', message: $message, signalFort: $signalFort);
    }
}
