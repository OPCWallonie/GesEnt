<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Services\ChantierMatcherService;
use App\Services\OcrFactureService;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    public function extract(Request $request, OcrFactureService $service, ChantierMatcherService $matcher)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
        ]);

        try {
            $data = $service->extraire($request->file('fichier'));

            // ── Résolution fournisseur par nom extrait ────────────────────────
            $fournisseurId = null;
            if (! empty($data['fournisseur_nom'])) {
                $fournisseur = Fournisseur::where(
                    'nom', 'like', '%' . like_escape($data['fournisseur_nom']) . '%'
                )->first();

                if ($fournisseur) {
                    $fournisseurId       = $fournisseur->id;
                    $data['fournisseur_id'] = $fournisseur->id;
                }
            }

            // ── Matching chantier ─────────────────────────────────────────────
            $match = $matcher->trouverChantier(
                $data['reference_chantier'] ?? null,
                $fournisseurId
            );

            if ($match) {
                $data['chantier_id']        = $match['chantier_id'];
                $data['chantier_confiance'] = $match['confiance'];
                $data['chantier_methode']   = $match['methode'];
                $data['chantier_message']   = $match['message'];
            }

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
