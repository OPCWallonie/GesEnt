<?php

namespace App\Http\Controllers;

use App\Services\VentesHistoriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VentesHistoriqueController extends Controller
{
    public function historique(Request $request, VentesHistoriqueService $service)
    {
        $data = $request->validate([
            'produit_id'         => 'nullable|integer|exists:produits,id',
            'catalog_produit_id' => 'nullable|integer|exists:catalog_produits,id',
            'designation'        => 'nullable|string|max:255',
            'client_id'          => 'nullable|integer|exists:clients,id',
        ]);

        $cacheKey = 'ventes_historique_' . auth()->id() . '_' . md5(json_encode($data));

        $result = Cache::remember($cacheKey, 120, fn() => $service->historique(
            $data['produit_id']         ?? null,
            $data['catalog_produit_id'] ?? null,
            $data['designation']        ?? null,
            $data['client_id']          ?? null,
        ));

        return response()->json($result);
    }
}
