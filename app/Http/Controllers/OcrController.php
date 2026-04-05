<?php

namespace App\Http\Controllers;

use App\Services\OcrFactureService;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    public function extract(Request $request, OcrFactureService $service)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
        ]);

        try {
            $data = $service->extraire($request->file('fichier'));
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
