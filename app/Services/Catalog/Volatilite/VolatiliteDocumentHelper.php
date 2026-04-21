<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use Illuminate\Database\Eloquent\Model;

class VolatiliteDocumentHelper
{
    public function __construct(
        private BadgeVolatiliteService        $badgeService,
        private ComparaisonFournisseursService $comparaisonService,
    ) {}

    /**
     * Prépare les badges et alternatives pour toutes les lignes d'un document.
     *
     * Retourne :
     *   badgesParProduit       : [catalog_produit_id => VolatiliteBadgeDTO]  (visibles seulement)
     *   alternativesParProduit : [catalog_produit_id => Collection<AlternativeFournisseurDTO>]
     */
    public function preparerPourDocument(Model $document): array
    {
        $params = ParametresEntreprise::instance();
        if (! $params->volatilite_active) {
            return ['badgesParProduit' => [], 'alternativesParProduit' => []];
        }

        $lignes = $document->lignes()
            ->whereNotNull('catalog_produit_id')
            ->where('est_section', false)
            ->get();

        if ($lignes->isEmpty()) {
            return ['badgesParProduit' => [], 'alternativesParProduit' => []];
        }

        $ids     = $lignes->pluck('catalog_produit_id')->unique()->filter();
        $produits = CatalogProduit::whereIn('id', $ids)->get()->keyBy('id');

        $badgesParProduit      = [];
        $alternativesParProduit = [];

        // Aggregate montants par produit pour le seuil de pertinence
        $montantsParProduit = $lignes->groupBy('catalog_produit_id')
            ->map(fn($group) => $group->sum('montant_ht'));

        foreach ($produits as $id => $produit) {
            // Dimension 1 : badge (soumis au filtre de pertinence seuil €)
            $montant = (float) ($montantsParProduit[$id] ?? 0);
            if ($this->badgeService->pertinentPourLigne($produit, $montant)) {
                $badge = $this->badgeService->composer($produit);
                if ($badge->visible()) {
                    $badgesParProduit[$id] = $badge;
                }
            }

            // Dimension 2 : alternatives EAN (indépendant du filtre badge)
            $alternatives = $this->comparaisonService->alternativesAvantageuses($produit);
            if ($alternatives->isNotEmpty()) {
                $alternativesParProduit[$id] = $alternatives;
            }
        }

        return [
            'badgesParProduit'      => $badgesParProduit,
            'alternativesParProduit' => $alternativesParProduit,
        ];
    }
}
