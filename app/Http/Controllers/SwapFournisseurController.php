<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduit;
use App\Models\LigneDocument;
use Illuminate\Http\Request;

class SwapFournisseurController extends Controller
{
    public function swap(Request $request, LigneDocument $ligneDocument)
    {
        $data = $request->validate([
            'catalog_produit_id' => 'required|integer|exists:catalog_produits,id',
        ]);

        $nouveauProduit = CatalogProduit::findOrFail($data['catalog_produit_id']);
        $ancienProduit  = $ligneDocument->catalogProduit;

        // Les deux produits doivent partager le même EAN
        if (
            !$nouveauProduit->ean
            || !$ancienProduit
            || !$ancienProduit->ean
            || $nouveauProduit->ean !== $ancienProduit->ean
        ) {
            return response()->json(['error' => 'Les produits ne partagent pas le même EAN.'], 422);
        }

        $ancienTva = (float) $ligneDocument->taux_tva;
        $nouveauTva = (float) $nouveauProduit->taux_tva;

        $ligneDocument->update([
            'catalog_produit_id' => $nouveauProduit->id,
            'designation'        => $nouveauProduit->designation,
            'unite'              => $nouveauProduit->unite,
            'prix_unitaire'      => $nouveauProduit->prix_revente,
            'taux_tva'           => $nouveauTva,
            'montant_ht'         => $ligneDocument->calculerMontantAvecPrix((float) $nouveauProduit->prix_revente),
        ]);

        $avertissement = null;
        if (abs($ancienTva - $nouveauTva) > 0.01) {
            $avertissement = "TVA modifiée de {$ancienTva}% à {$nouveauTva}%";
        }

        return response()->json([
            'success'      => true,
            'avertissement'=> $avertissement,
        ]);
    }
}
