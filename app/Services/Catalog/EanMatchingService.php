<?php

namespace App\Services\Catalog;

use App\Models\CatalogProduit;
use Illuminate\Support\Collection;

class EanMatchingService
{
    /**
     * Retourne les alternatives (autres fournisseurs) pour un CatalogProduit donné.
     * Match strict sur l'EAN, exclut le produit lui-même, trie par prix catalogue croissant.
     *
     * @return Collection<CatalogProduit>
     */
    public function equivalentsAutresFournisseurs(CatalogProduit $produit): Collection
    {
        if (empty($produit->ean)) {
            return collect();
        }

        return CatalogProduit::where('ean', $produit->ean)
            ->where('id', '!=', $produit->id)
            ->orderBy('prix_catalogue')
            ->get();
    }

    /**
     * Pour un ensemble de produits (résultats de recherche), regroupe ceux qui
     * partagent un EAN. Retourne un array indexé par EAN.
     *
     * @param Collection<CatalogProduit> $produits
     * @return array ['ean1' => [produits...], 'sans_ean' => [...]]
     */
    public function regrouperParEan(Collection $produits): array
    {
        $groupes = ['sans_ean' => []];

        foreach ($produits as $produit) {
            $ean = $produit->ean;
            if (empty($ean)) {
                $groupes['sans_ean'][] = $produit;
                continue;
            }
            $groupes[$ean] ??= [];
            $groupes[$ean][] = $produit;
        }

        foreach ($groupes as &$items) {
            usort($items, fn($a, $b) => $a->prix_catalogue <=> $b->prix_catalogue);
        }

        return $groupes;
    }

    /**
     * Combien de fournisseurs distincts proposent un EAN donné.
     */
    public function nbFournisseursPourEan(string $ean): int
    {
        return CatalogProduit::where('ean', $ean)
            ->distinct('fournisseur')
            ->count('fournisseur');
    }
}
