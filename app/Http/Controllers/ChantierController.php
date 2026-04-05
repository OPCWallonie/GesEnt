<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Client;
use Illuminate\Http\Request;

class ChantierController extends Controller
{
    public function index(Request $request)
    {
        $chantiers = Chantier::with('client')
            ->when($request->q, fn($q, $s) => $q->where('nom', 'like', "%{$s}%"))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $clients = Client::orderBy('nom')->get(['id', 'nom']);

        return view('chantiers.index', compact('chantiers', 'clients'));
    }

    public function create(Request $request)
    {
        $clients = Client::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $clientSelectionne = $request->client_id ? Client::find($request->client_id) : null;
        return view('chantiers.create', compact('clients', 'clientSelectionne'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'nom'              => 'required|string|max:150',
            'description'      => 'nullable|string',
            'adresse_chantier' => 'nullable|string|max:150',
            'code_postal'      => 'nullable|string|max:10',
            'ville'            => 'nullable|string|max:80',
            'pays'             => 'nullable|string|max:60',
            'statut'           => 'required|in:actif,inactif,termine,archive',
            'avancement'       => 'nullable|integer|min:0|max:100',
            'date_debut'       => 'nullable|date',
            'date_fin_prevue'  => 'nullable|date|after_or_equal:date_debut',
            'date_debut_reel'  => 'nullable|date',
            'date_fin_reelle'  => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $chantier = Chantier::create($data);

        return redirect()->route('chantiers.show', $chantier)
            ->with('success', "Chantier « {$chantier->nom} » créé.");
    }

    public function show(Chantier $chantier)
    {
        $chantier->load('client', 'journal.user');
        $devis         = $chantier->devis()->with('client')->latest()->get();
        $bonsCommande  = $chantier->bonsCommande()->with('client')->latest()->get();
        $factures      = $chantier->factures()->latest('date_document')->get();
        $facturesAchat = $chantier->facturesAchat()->with('fournisseur')->latest('date_document')->get();

        return view('chantiers.show', compact('chantier', 'devis', 'bonsCommande', 'factures', 'facturesAchat'));
    }

    public function edit(Chantier $chantier)
    {
        $clients = Client::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        return view('chantiers.edit', compact('chantier', 'clients'));
    }

    public function update(Request $request, Chantier $chantier)
    {
        $data = $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'nom'              => 'required|string|max:150',
            'description'      => 'nullable|string',
            'adresse_chantier' => 'nullable|string|max:150',
            'code_postal'      => 'nullable|string|max:10',
            'ville'            => 'nullable|string|max:80',
            'pays'             => 'nullable|string|max:60',
            'statut'           => 'required|in:actif,inactif,termine,archive',
            'avancement'       => 'nullable|integer|min:0|max:100',
            'date_debut'       => 'nullable|date',
            'date_fin_prevue'  => 'nullable|date|after_or_equal:date_debut',
            'date_debut_reel'  => 'nullable|date',
            'date_fin_reelle'  => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $chantier->update($data);

        return redirect()->route('chantiers.show', $chantier)
            ->with('success', 'Chantier mis à jour.');
    }

    public function destroy(Chantier $chantier)
    {
        $chantier->delete();
        return redirect()->route('chantiers.index')
            ->with('success', "Chantier « {$chantier->nom} » supprimé.");
    }
}
