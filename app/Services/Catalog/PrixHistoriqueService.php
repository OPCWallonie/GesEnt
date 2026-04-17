<?php

namespace App\Services\Catalog;

use App\Models\CatalogProduit;
use App\Models\CatalogPrixHistorique;

class PrixHistoriqueService
{
    /**
     * Enregistre un changement de prix si nécessaire.
     * Doit être appelé AVANT updateOrCreate pour capturer l'ancien prix.
     *
     * @param CatalogProduit|null $produitExistant  produit actuellement en BDD (null si création)
     * @param float               $nouveauPrix       nouveau prix catalogue
     * @param string              $source            'csv' ou 'api'
     * @return bool true si un changement a été historisé
     */
    public function enregistrerSiChange(
        ?CatalogProduit $produitExistant,
        float $nouveauPrix,
        string $source
    ): bool {
        if (!$produitExistant) {
            return false;
        }

        $prixAvant = (float) $produitExistant->prix_catalogue;

        if (abs($prixAvant - $nouveauPrix) < 0.0001) {
            return false;
        }

        $variationPct = $prixAvant > 0
            ? round(($nouveauPrix - $prixAvant) / $prixAvant * 100, 2)
            : 0;

        CatalogPrixHistorique::create([
            'catalog_produit_id' => $produitExistant->id,
            'fournisseur'        => $produitExistant->fournisseur,
            'reference'          => $produitExistant->reference,
            'prix_avant'         => $prixAvant,
            'prix_apres'         => $nouveauPrix,
            'variation_pct'      => $variationPct,
            'est_significatif'   => abs($variationPct) >= CatalogProduit::SEUIL_VARIATION_SIGNIFICATIVE,
            'source'             => $source,
            'detected_at'        => now(),
        ]);

        return true;
    }
}
