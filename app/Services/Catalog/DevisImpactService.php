<?php

namespace App\Services\Catalog;

use App\Models\CatalogPrixHistorique;
use App\Models\Devis;
use App\Models\LigneDocument;
use Illuminate\Support\Collection;

class DevisImpactService
{
    /**
     * IDs des catalog_produits ayant eu un changement significatif depuis $depuis.
     */
    public function produitsAvecChangementSignificatif(?\Carbon\Carbon $depuis = null): Collection
    {
        $depuis ??= now()->subMonths(3);

        return CatalogPrixHistorique::significatifs()
            ->where('detected_at', '>=', $depuis)
            ->pluck('catalog_produit_id')
            ->unique();
    }

    /**
     * Pour un devis donné, retourne les lignes impactées par un changement
     * de prix fournisseur postérieur à la création du devis.
     *
     * @return array<int, array> index = ligne_id
     */
    public function lignesImpactees(Devis $devis): array
    {
        $lignes = $devis->lignes()
            ->whereNotNull('catalog_produit_id')
            ->with('catalogProduit')
            ->get();

        $impactees = [];
        foreach ($lignes as $ligne) {
            $cp = $ligne->catalogProduit;
            if (!$cp) continue;

            $dernier = CatalogPrixHistorique::where('catalog_produit_id', $cp->id)
                ->significatifs()
                ->where('detected_at', '>', $devis->created_at)
                ->orderByDesc('detected_at')
                ->first();

            if ($dernier) {
                $impactees[$ligne->id] = [
                    'ligne'                  => $ligne,
                    'prix_devis'             => (float) $ligne->prix_unitaire,
                    'prix_catalogue_actuel'  => (float) $cp->prix_catalogue,
                    'variation_pct'          => (float) $dernier->variation_pct,
                    'detected_at'            => $dernier->detected_at,
                ];
            }
        }

        return $impactees;
    }

    /**
     * Compte le nombre de devis actifs impactés par des changements significatifs.
     * Devis actifs = statut ∈ {brouillon, en_attente, valide}
     *
     * @return array<int, int> [devis_id => nb_lignes_impactees]
     */
    public function devisActifsImpactes(): array
    {
        $produitIds = $this->produitsAvecChangementSignificatif();
        if ($produitIds->isEmpty()) return [];

        return LigneDocument::whereIn('catalog_produit_id', $produitIds)
            ->whereHasMorph('documentable', Devis::class, function ($q) {
                $q->whereIn('statut', ['brouillon', 'en_attente', 'valide']);
            })
            ->selectRaw('documentable_id, COUNT(*) as nb')
            ->groupBy('documentable_id')
            ->pluck('nb', 'documentable_id')
            ->toArray();
    }
}
