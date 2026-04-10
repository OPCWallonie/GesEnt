<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        $fournisseurs = Fournisseur::withCount('facturesAchat')
            ->when($request->q, fn($q, $s) => $q->where('nom', 'like', '%' . like_escape($s) . '%')
                ->orWhere('numero_tva', 'like', '%' . like_escape($s) . '%'))
            ->when($request->has('inactifs'), fn($q) => $q, fn($q) => $q->where('actif', true))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function create()
    {
        return view('fournisseurs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'                => 'required|string|max:100',
            'contact'            => 'nullable|string|max:100',
            'email'              => 'nullable|email|max:120',
            'telephone'          => 'nullable|string|max:30',
            'numero_tva'         => 'nullable|string|max:30',
            'numero_entreprise'  => 'nullable|string|max:30',
            'adresse'            => 'nullable|string|max:150',
            'code_postal'        => 'nullable|string|max:10',
            'ville'              => 'nullable|string|max:80',
            'pays'               => 'nullable|string|max:60',
            'notes'              => 'nullable|string',
            'actif'              => 'boolean',
        ]);

        $fournisseur = Fournisseur::create($data);

        return redirect()->route('fournisseurs.show', $fournisseur)
            ->with('success', "Fournisseur {$fournisseur->nom} créé.");
    }

    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->loadCount('facturesAchat');
        $derniereFactures = $fournisseur->facturesAchat()
            ->with('chantier')
            ->orderByDesc('date_document')
            ->take(10)
            ->get();

        $totalTTC    = $fournisseur->facturesAchat()->sum('montant_ttc');
        $totalEnCours = $fournisseur->facturesAchat()->where('statut', 'en_attente')->sum('montant_ttc');

        return view('fournisseurs.show', compact('fournisseur', 'derniereFactures', 'totalTTC', 'totalEnCours'));
    }

    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $data = $request->validate([
            'nom'                => 'required|string|max:100',
            'contact'            => 'nullable|string|max:100',
            'email'              => 'nullable|email|max:120',
            'telephone'          => 'nullable|string|max:30',
            'numero_tva'         => 'nullable|string|max:30',
            'numero_entreprise'  => 'nullable|string|max:30',
            'adresse'            => 'nullable|string|max:150',
            'code_postal'        => 'nullable|string|max:10',
            'ville'              => 'nullable|string|max:80',
            'pays'               => 'nullable|string|max:60',
            'notes'              => 'nullable|string',
            'actif'              => 'boolean',
        ]);

        $data['actif'] = $request->boolean('actif');
        $fournisseur->update($data);

        return redirect()->route('fournisseurs.show', $fournisseur)
            ->with('success', 'Fournisseur mis à jour.');
    }

    public function apiSearch(Request $request)
    {
        $q     = $request->get('q', '');
        $all   = $request->boolean('all');
        $limit = $all ? 50 : 15;
        return response()->json(
            Fournisseur::query()
                ->when($q, fn($query) => $query->where('nom', 'like', '%' . like_escape($q) . '%'))
                ->where('actif', true)
                ->when(!$q, fn($query) => $query->orderByDesc('updated_at'))
                ->when($q,  fn($query) => $query->orderBy('nom'))
                ->limit($limit)
                ->get(['id', 'nom'])
        );
    }

    public function quickCreate(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'email'     => 'nullable|email|max:120',
            'telephone' => 'nullable|string|max:30',
        ]);

        $fournisseur = Fournisseur::create(array_merge($data, ['actif' => true]));

        return response()->json(['id' => $fournisseur->id, 'nom' => $fournisseur->nom]);
    }

    public function destroy(Fournisseur $fournisseur)
    {
        if ($fournisseur->facturesAchat()->exists()) {
            return back()->with('error', 'Impossible de supprimer un fournisseur avec des factures.');
        }
        $fournisseur->delete();
        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur supprimé.');
    }
}
