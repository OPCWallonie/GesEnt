<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\JournalChantier;
use App\Models\User;
use App\Notifications\NouveauJournalChantier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JournalChantierController extends Controller
{
    public function store(Request $request, Chantier $chantier)
    {
        $data = $request->validate([
            'type'             => 'required|in:note,photo,jalon,probleme,reunion,livraison',
            'titre'            => 'nullable|string|max:255',
            'contenu'          => 'nullable|string',
            'avancement_apres' => 'nullable|integer|min:0|max:100',
            'photos.*'         => 'nullable|image|max:5120',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store("chantiers/{$chantier->id}/journal", 'public');
            }
        }

        $entree = $chantier->journal()->create([
            'user_id'          => auth()->id(),
            'type'             => $data['type'],
            'titre'            => $data['titre'] ?? null,
            'contenu'          => $data['contenu'] ?? null,
            'photos'           => $photos ?: null,
            'avancement_apres' => $data['avancement_apres'] ?? null,
        ]);

        // Mettre à jour l'avancement du chantier si fourni
        if (!empty($data['avancement_apres'])) {
            $chantier->update(['avancement' => $data['avancement_apres']]);
        }

        // Notifier les admins/comptables (sauf l'auteur)
        $admins = User::role(['admin', 'comptable'])
            ->where('id', '!=', auth()->id())
            ->get();
        foreach ($admins as $admin) {
            $admin->notify(new NouveauJournalChantier($entree));
        }

        return back()->with('success', 'Entrée ajoutée au journal.');
    }

    public function destroy(JournalChantier $journal)
    {
        // Supprimer les photos associées
        if ($journal->photos) {
            foreach ($journal->photos as $path) {
                Storage::disk('public')->delete($path);
            }
        }
        $journal->delete();
        return back()->with('success', 'Entrée supprimée.');
    }
}
