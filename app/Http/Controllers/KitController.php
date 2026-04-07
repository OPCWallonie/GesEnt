<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use Illuminate\Http\Request;

class KitController extends Controller
{
    public function index(Request $request)
    {
        $kits = Kit::withCount('lignes')
            ->when($request->q, fn($q, $s) => $q->where('nom', 'like', '%' . like_escape($s) . '%'))
            ->when($request->categorie, fn($q, $c) => $q->where('categorie', $c))
            ->orderByDesc('nb_utilisations')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        $categories = Kit::whereNotNull('categorie')
            ->distinct()->orderBy('categorie')
            ->pluck('categorie');

        return view('kits.index', compact('kits', 'categories'));
    }

    public function create()
    {
        return view('kits.create', ['kit' => new Kit()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'                    => 'required|string|max:150',
            'description'            => 'nullable|string|max:500',
            'categorie'              => 'nullable|string|max:80',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.detail'        => 'nullable|string',
            'lignes.*.unite'         => 'nullable|string|max:20',
            'lignes.*.quantite'      => 'nullable|numeric|min:0',
            'lignes.*.prix_unitaire' => 'nullable|numeric|min:0',
            'lignes.*.remise_valeur' => 'nullable|numeric|min:0',
            'lignes.*.remise_type'   => 'nullable|in:montant,pourcentage',
            'lignes.*.taux_tva'      => 'nullable|numeric',
            'lignes.*.est_section'   => 'nullable',
        ]);

        $kit = Kit::create([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'categorie'   => $data['categorie'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        foreach ($data['lignes'] as $ordre => $ligneData) {
            $kit->lignes()->create([
                'ordre'         => $ordre,
                'est_section'   => !empty($ligneData['est_section']),
                'designation'   => $ligneData['designation'],
                'detail'        => $ligneData['detail'] ?? null,
                'unite'         => $ligneData['unite'] ?? 'pièce',
                'quantite'      => $ligneData['quantite'] ?? 1,
                'prix_unitaire' => $ligneData['prix_unitaire'] ?? 0,
                'remise_valeur' => $ligneData['remise_valeur'] ?? 0,
                'remise_type'   => $ligneData['remise_type'] ?? 'montant',
                'taux_tva'      => $ligneData['taux_tva'] ?? 21,
            ]);
        }

        return redirect()->route('kits.index')
            ->with('success', "Kit « {$kit->nom} » créé avec {$kit->lignes->count()} lignes.");
    }

    public function show(Kit $kit)
    {
        $kit->load('lignes');
        return view('kits.show', compact('kit'));
    }

    public function edit(Kit $kit)
    {
        $kit->load('lignes');

        $lignesCompatibles = $kit->lignes->map(fn($l) => (object) [
            'designation'        => $l->designation,
            'detail'             => $l->detail,
            'unite'              => $l->unite,
            'quantite'           => $l->quantite,
            'prix_unitaire'      => $l->prix_unitaire,
            'remise_valeur'      => $l->remise_valeur,
            'remise_type'        => $l->remise_type,
            'taux_tva'           => $l->taux_tva,
            'est_section'        => $l->est_section,
            'montant_ht'         => 0,
            'produit_id'         => $l->produit_id,
            'catalog_produit_id' => $l->catalog_produit_id,
        ]);

        return view('kits.edit', compact('kit', 'lignesCompatibles'));
    }

    public function update(Request $request, Kit $kit)
    {
        $data = $request->validate([
            'nom'                    => 'required|string|max:150',
            'description'            => 'nullable|string|max:500',
            'categorie'              => 'nullable|string|max:80',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.detail'        => 'nullable|string',
            'lignes.*.unite'         => 'nullable|string|max:20',
            'lignes.*.quantite'      => 'nullable|numeric|min:0',
            'lignes.*.prix_unitaire' => 'nullable|numeric|min:0',
            'lignes.*.remise_valeur' => 'nullable|numeric|min:0',
            'lignes.*.remise_type'   => 'nullable|in:montant,pourcentage',
            'lignes.*.taux_tva'      => 'nullable|numeric',
            'lignes.*.est_section'   => 'nullable',
        ]);

        $kit->update([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'categorie'   => $data['categorie'] ?? null,
        ]);

        $kit->lignes()->delete();
        foreach ($data['lignes'] as $ordre => $ligneData) {
            $kit->lignes()->create([
                'ordre'         => $ordre,
                'est_section'   => !empty($ligneData['est_section']),
                'designation'   => $ligneData['designation'],
                'detail'        => $ligneData['detail'] ?? null,
                'unite'         => $ligneData['unite'] ?? 'pièce',
                'quantite'      => $ligneData['quantite'] ?? 1,
                'prix_unitaire' => $ligneData['prix_unitaire'] ?? 0,
                'remise_valeur' => $ligneData['remise_valeur'] ?? 0,
                'remise_type'   => $ligneData['remise_type'] ?? 'montant',
                'taux_tva'      => $ligneData['taux_tva'] ?? 21,
            ]);
        }

        return redirect()->route('kits.show', $kit)
            ->with('success', "Kit « {$kit->nom} » mis à jour.");
    }

    public function destroy(Kit $kit)
    {
        $nom = $kit->nom;
        $kit->lignes()->delete();
        $kit->delete();
        return redirect()->route('kits.index')
            ->with('success', "Kit « {$nom} » supprimé.");
    }

    public function apiList(Request $request)
    {
        $kits = Kit::withCount('lignes')
            ->when($request->q, fn($q, $s) => $q->where('nom', 'like', '%' . like_escape($s) . '%'))
            ->orderByDesc('nb_utilisations')
            ->limit(20)
            ->get(['id', 'nom', 'description', 'categorie', 'nb_utilisations']);

        return response()->json($kits->map(fn($k) => [
            'id'              => $k->id,
            'nom'             => $k->nom,
            'description'     => $k->description,
            'categorie'       => $k->categorie,
            'nb_lignes'       => $k->lignes_count,
            'nb_utilisations' => $k->nb_utilisations,
            'estimation_ht'   => $k->estimationHt(),
        ]));
    }

    public function apiLignes(Kit $kit)
    {
        $kit->increment('nb_utilisations');
        $kit->load('lignes');

        $lignes = $kit->lignes->map(fn($l) => [
            'designation'        => $l->designation,
            'detail'             => $l->detail ?? '',
            'unite'              => $l->unite,
            'quantite'           => (float) $l->quantite,
            'prix_unitaire'      => (float) $l->prix_unitaire,
            'remise_valeur'      => (float) $l->remise_valeur,
            'remise_type'        => $l->remise_type,
            'taux_tva'           => (float) $l->taux_tva,
            'est_section'        => $l->est_section,
            'montant_ht'         => 0,
            'produit_id'         => $l->produit_id,
            'catalog_produit_id' => $l->catalog_produit_id,
            'produit_key'        => $l->produit_id ? 'p:' . $l->produit_id : ($l->catalog_produit_id ? 'c:' . $l->catalog_produit_id : ''),
        ]);

        return response()->json($lignes);
    }
}
