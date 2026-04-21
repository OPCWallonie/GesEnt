<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use Illuminate\Support\Collection;

class GroupeComparaisonResolver
{
    public function resoudre(CatalogProduit $produit): array
    {
        $base = CatalogProduit::where('fournisseur', $produit->fournisseur)
            ->where('id', '!=', $produit->id)
            ->whereNotNull('volatilite_tendance_pct');

        if ($produit->sous_categorie) {
            $candidats = (clone $base)->where('sous_categorie', $produit->sous_categorie)->get();
            if ($candidats->count() >= 5) {
                return [$candidats, 'sous_categorie'];
            }
        }

        if ($produit->categorie) {
            $candidats = (clone $base)->where('categorie', $produit->categorie)->get();
            if ($candidats->count() >= 5) {
                return [$candidats, 'categorie'];
            }
        }

        $candidats = $base->get();
        if ($candidats->count() >= 5) {
            return [$candidats, 'fournisseur'];
        }

        $candidats = CatalogProduit::where('id', '!=', $produit->id)
            ->whereNotNull('volatilite_tendance_pct')
            ->get();

        return [$candidats, 'catalogue'];
    }

    public function mediane(array $valeurs): ?float
    {
        $valeurs = array_filter($valeurs, fn($v) => $v !== null);
        if (empty($valeurs)) return null;

        sort($valeurs);
        $n = count($valeurs);
        $milieu = (int) floor($n / 2);

        if ($n % 2 === 1) {
            return $valeurs[$milieu];
        }

        return ($valeurs[$milieu - 1] + $valeurs[$milieu]) / 2;
    }
}
