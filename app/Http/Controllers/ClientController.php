<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->q, fn($q, $search) => $q->where('nom', 'like', "%{$search}%")
                ->orWhere('code_client', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->withCount('devis', 'factures')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create', ['client' => new Client()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'                 => 'required|string|max:100',
            'statut_juridique'    => 'nullable|string|max:10',
            'adresse'             => 'nullable|string|max:150',
            'code_postal'         => 'nullable|string|max:10',
            'ville'               => 'nullable|string|max:80',
            'pays'                => 'nullable|string|max:60',
            'telephone'           => 'nullable|string|max:30',
            'fax'                 => 'nullable|string|max:20',
            'gsm'                 => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:120',
            'site_web'            => 'nullable|url|max:100',
            'numero_tva'          => 'nullable|string|max:30|unique:clients',
            'numero_affiliation'  => 'nullable|string|max:20',
            'code_client'         => 'nullable|string|max:20|unique:clients',
            'notes'               => 'nullable|string',
            'coefficient_marge'   => 'nullable|numeric|min:0|max:200',
        ]);

        $client = Client::create($data);

        return redirect()->route('clients.show', $client)
            ->with('success', "Client « {$client->nom} » créé avec succès.");
    }

    public function show(Client $client)
    {
        $client->loadCount('devis', 'bonsCommande', 'factures');

        $chantiers       = $client->chantiers()->withCount('devis')->get();
        $derniersDevis   = $client->devis()->with('chantier')->latest()->take(5)->get();
        $derniersFactures = $client->factures()->latest('date_document')->take(5)->get();

        // CA total : somme de toutes les factures (hors archive)
        $caTotalTtc = $client->factures()
            ->whereIn('statut', ['en_attente', 'envoyee', 'payee'])
            ->sum('montant_ttc');

        $caEncaisse = $client->factures()
            ->where('statut', 'payee')
            ->sum('montant_paye');

        $enCours = $client->factures()
            ->whereIn('statut', ['en_attente', 'envoyee'])
            ->sum('montant_net_a_payer');

        return view('clients.show', compact(
            'client', 'chantiers', 'derniersDevis', 'derniersFactures',
            'caTotalTtc', 'caEncaisse', 'enCours'
        ));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'nom'                 => 'required|string|max:100',
            'statut_juridique'    => 'nullable|string|max:10',
            'adresse'             => 'nullable|string|max:150',
            'code_postal'         => 'nullable|string|max:10',
            'ville'               => 'nullable|string|max:80',
            'pays'                => 'nullable|string|max:60',
            'telephone'           => 'nullable|string|max:30',
            'fax'                 => 'nullable|string|max:20',
            'gsm'                 => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:120',
            'site_web'            => 'nullable|url|max:100',
            'numero_tva'          => 'nullable|string|max:30|unique:clients,numero_tva,' . $client->id,
            'numero_affiliation'  => 'nullable|string|max:20',
            'code_client'         => 'nullable|string|max:20|unique:clients,code_client,' . $client->id,
            'notes'               => 'nullable|string',
            'coefficient_marge'   => 'nullable|numeric|min:0|max:200',
        ]);

        $client->update($data);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', "Client « {$client->nom} » supprimé.");
    }

    public function chantiers(Client $client)
    {
        return response()->json(
            $client->chantiers()
                ->where('statut', 'actif')
                ->select('id', 'nom', 'coefficient_marge')
                ->orderBy('nom')
                ->get()
                ->map(fn($c) => [
                    'id'               => $c->id,
                    'nom'              => $c->nom,
                    'coefficient_marge' => (float) ($c->coefficient_marge ?? $client->coefficient_marge ?? 0),
                ])
        );
    }
}
