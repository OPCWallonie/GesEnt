<?php

namespace App\Http\Controllers;

use App\Models\DocumentDraft;
use Illuminate\Http\Request;

class DocumentDraftController extends Controller
{
    private const TYPES_VALIDES = ['devis', 'bon_commande', 'facture'];

    public function save(Request $request)
    {
        $data = $request->validate([
            'document_type' => 'required|in:' . implode(',', self::TYPES_VALIDES),
            'document_id'   => 'nullable|integer',
            'data'          => 'required|array',
        ]);

        if (strlen(json_encode($data['data'])) > 500_000) {
            return response()->json(['error' => 'Draft trop volumineux'], 413);
        }

        $draft = DocumentDraft::updateOrCreate(
            [
                'user_id'       => auth()->id(),
                'document_type' => $data['document_type'],
                'document_id'   => $data['document_id'] ?? null,
            ],
            [
                'data'     => $data['data'],
                'saved_at' => now(),
            ]
        );

        return response()->json([
            'ok'       => true,
            'saved_at' => $draft->saved_at->toIso8601String(),
        ]);
    }

    public function load(Request $request)
    {
        $data = $request->validate([
            'document_type' => 'required|in:' . implode(',', self::TYPES_VALIDES),
            'document_id'   => 'nullable|integer',
        ]);

        $draft = DocumentDraft::pourUser(auth()->id())
            ->where('document_type', $data['document_type'])
            ->where('document_id', $data['document_id'] ?? null)
            ->recent(48)
            ->first();

        if (!$draft) {
            return response()->json(['draft' => null]);
        }

        return response()->json([
            'draft' => [
                'data'         => $draft->data,
                'saved_at'     => $draft->saved_at->toIso8601String(),
                'age_minutes'  => (int) $draft->saved_at->diffInMinutes(now()),
            ],
        ]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'document_type' => 'required|in:' . implode(',', self::TYPES_VALIDES),
            'document_id'   => 'nullable|integer',
        ]);

        DocumentDraft::pourUser(auth()->id())
            ->where('document_type', $data['document_type'])
            ->where('document_id', $data['document_id'] ?? null)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
