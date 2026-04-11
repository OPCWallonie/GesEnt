<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChantierController extends Controller
{
    public function index(Request $request)
    {
        $chantiers = Chantier::with('client')
            ->when($request->q, fn($q, $s) => $q->where('nom', 'like', '%' . like_escape($s) . '%'))
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
        $clientId = $request->client_id ?: old('client_id');
        $clientSelectionne = $clientId ? Client::find($clientId) : null;
        return view('chantiers.create', compact('clients', 'clientSelectionne'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'nom'              => 'required|string|max:150',
            'reference'        => 'nullable|string|max:20|unique:chantiers,reference',
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
            'date_fin_reelle'    => 'nullable|date',
            'notes'              => 'nullable|string',
            'coefficient_marge'  => 'nullable|numeric|min:0|max:200',
        ]);

        $chantier = Chantier::create($data);

        return redirect()->route('chantiers.show', $chantier)
            ->with('success', "Chantier « {$chantier->nom} » créé.");
    }

    public function apiSearch(Request $request)
    {
        $q        = $request->get('q', '');
        $clientId = $request->get('client_id');
        $all      = $request->boolean('all');
        $limit    = $all ? 50 : 15;
        return response()->json(
            Chantier::query()
                ->when($clientId, fn($query) => $query->where('client_id', $clientId))
                ->when($q, fn($query) => $query->where(function ($q2) use ($q) {
                    $escaped = like_escape($q);
                    $q2->where('nom', 'like', '%' . $escaped . '%')
                       ->orWhere('reference', 'like', '%' . $escaped . '%');
                }))
                ->when(! $q, fn($query) => $query->orderByDesc('updated_at'))
                ->when($q,   fn($query) => $query->orderBy('nom'))
                ->limit($limit)
                ->get(['id', 'nom', 'reference', 'coefficient_marge', 'client_id'])
                ->map(fn($c) => [
                    'id'                => $c->id,
                    'nom'               => $c->reference ? "[{$c->reference}] {$c->nom}" : $c->nom,
                    'coefficient_marge' => (float) $c->coefficient_marge,
                ])
        );
    }

    public function quickCreate(Request $request, Client $client)
    {
        $data = $request->validate([
            'nom'             => 'required|string|max:150',
            'adresse_chantier'=> 'nullable|string|max:150',
            'ville'           => 'nullable|string|max:80',
        ]);

        $chantier = Chantier::create(array_merge($data, [
            'client_id' => $client->id,
            'statut'    => 'actif',
        ]));

        return response()->json([
            'id'               => $chantier->id,
            'nom'              => $chantier->nom,
            'coefficient_marge' => $chantier->coefficientMargeEffectif(),
        ]);
    }

    public function show(Chantier $chantier)
    {
        $chantier->load('client', 'journal.user');
        $devis         = $chantier->devis()->with('client')->latest()->get();
        $bonsCommande  = $chantier->bonsCommande()->with('client')->latest()->get();
        $factures      = $chantier->factures()->latest('date_document')->get();
        $facturesAchat = $chantier->facturesAchat()->with('fournisseur')->latest('date_document')->get();

        // Graphique avancement financier mensuel
        $moisLabels    = [];
        $ventesParMois = [];
        $achatsParMois = [];
        $margeCumulee  = [];

        $premiereFacture      = $chantier->factures()->orderBy('date_document')->first();
        $premiereFactureAchat = $chantier->facturesAchat()->orderBy('date_document')->first();
        $debut = collect([$premiereFacture?->date_document, $premiereFactureAchat?->date_document])
            ->filter()->min();

        if ($debut) {
            $debut       = Carbon::parse($debut)->startOfMonth();
            $fin         = now()->endOfMonth();
            $cumulVentes = 0;
            $cumulAchats = 0;
            $cursor      = $debut->copy();

            while ($cursor <= $fin) {
                $debutMois = $cursor->copy()->startOfMonth();
                $finMois   = $cursor->copy()->endOfMonth();

                $ventesMois = $chantier->factures()
                    ->whereIn('statut', ['en_attente', 'envoyee', 'payee', 'en_retard'])
                    ->whereBetween('date_document', [$debutMois, $finMois])
                    ->sum('montant_ttc');

                $achatsMois = $chantier->facturesAchat()
                    ->whereBetween('date_document', [$debutMois, $finMois])
                    ->sum('montant_ttc');

                $cumulVentes += $ventesMois;
                $cumulAchats += $achatsMois;

                $moisLabels[]    = $cursor->locale('fr')->isoFormat('MMM YY');
                $ventesParMois[] = round($cumulVentes, 2);
                $achatsParMois[] = round($cumulAchats, 2);
                $margeCumulee[]  = round($cumulVentes - $cumulAchats, 2);

                $cursor->addMonth();
            }
        }

        // Barres de progression facturation / encaissement
        $ventes     = $chantier->totalVentes();
        $pctFacture = 0;
        $pctPaye    = 0;
        if ($ventes > 0 || $bonsCommande->isNotEmpty()) {
            $totalBdc = $bonsCommande->sum(fn($b) => $b->montantTotalAvecAvenants()['ttc']);
            if ($totalBdc > 0) {
                $pctFacture = min(100, ($ventes / $totalBdc) * 100);
                $totalPaye  = $chantier->factures()->where('statut', 'payee')->sum('montant_paye');
                $pctPaye    = min(100, ($totalPaye / $totalBdc) * 100);
            }
        }

        // Main d'œuvre
        $annee          = now()->year;
        $coutMo         = $chantier->coutMainOeuvre($annee);
        $margeReelle    = $chantier->margeReelle($annee);
        $tauxMargeReelle = $chantier->tauxMargeReelle($annee);

        $pointagesParOuvrier = $chantier->pointages()
            ->with('ouvrier')
            ->whereYear('date', $annee)
            ->get()
            ->groupBy('ouvrier_id')
            ->map(fn($rows) => [
                'ouvrier'    => $rows->first()->ouvrier,
                'heures'     => $rows->sum('heures'),
                'heures_sup' => $rows->sum('heures_sup'),
                'cout_total' => $rows->sum('cout_total'),
            ])
            ->sortByDesc('cout_total')
            ->values();

        return view('chantiers.show', compact(
            'chantier', 'devis', 'bonsCommande', 'factures', 'facturesAchat',
            'moisLabels', 'ventesParMois', 'achatsParMois', 'margeCumulee',
            'pctFacture', 'pctPaye',
            'coutMo', 'margeReelle', 'tauxMargeReelle', 'pointagesParOuvrier', 'annee'
        ));
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
            'reference'        => "nullable|string|max:20|unique:chantiers,reference,{$chantier->id}",
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
            'date_fin_reelle'    => 'nullable|date',
            'notes'              => 'nullable|string',
            'coefficient_marge'  => 'nullable|numeric|min:0|max:200',
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
