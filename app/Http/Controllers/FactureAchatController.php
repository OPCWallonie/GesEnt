<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Chantier;
use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Services\NumerotationService;
use Illuminate\Http\Request;

class FactureAchatController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function index(Request $request)
    {
        $factures = FactureAchat::with('fournisseur', 'chantier')
            ->when($request->q, fn($q, $s) => $q->where('numero', 'like', '%' . like_escape($s) . '%')
                ->orWhereHas('fournisseur', fn($q) => $q->where('nom', 'like', '%' . like_escape($s) . '%'))
                ->orWhere('reference_fournisseur', 'like', '%' . like_escape($s) . '%'))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->when($request->categorie, fn($q, $c) => $q->where('categorie', $c))
            ->when($request->fournisseur_id, fn($q, $f) => $q->where('fournisseur_id', $f))
            ->orderByDesc('date_document')
            ->paginate(20)
            ->withQueryString();

        $totalEnCours = FactureAchat::where('statut', 'en_attente')->sum('montant_ttc');
        $fournisseurs = Fournisseur::actif()->orderBy('nom')->get();

        return view('factures-achat.index', compact('factures', 'totalEnCours', 'fournisseurs'));
    }

    public function create(Request $request)
    {
        $fournisseurs  = Fournisseur::actif()->orderBy('nom')->get();
        $chantiers     = Chantier::with('client')->orderByDesc('id')->get();
        $bonsCommande  = BonCommande::with('client')->orderByDesc('id')->take(50)->get();
        $fournisseurId = $request->fournisseur_id;
        $chantierId    = $request->chantier_id;

        return view('factures-achat.create', compact('fournisseurs', 'chantiers', 'bonsCommande', 'fournisseurId', 'chantierId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fournisseur_id'         => 'required|exists:fournisseurs,id',
            'chantier_id'            => 'nullable|exists:chantiers,id',
            'bon_commande_id'        => 'nullable|exists:bons_commande,id',
            'reference_fournisseur'  => 'nullable|string|max:60',
            'categorie'              => 'required|in:materiel,sous_traitance,frais_generaux,divers',
            'date_document'          => 'required|date',
            'date_echeance'          => 'nullable|date',
            'montant_ht'             => 'required|numeric|min:0',
            'taux_tva'               => 'required|numeric|min:0|max:100',
            'statut'                 => 'required|in:en_attente,payee',
            'date_paiement'          => 'nullable|date',
            'notes'                  => 'nullable|string',
        ]);

        $ht  = (float) $data['montant_ht'];
        $tva = $ht * ($data['taux_tva'] / 100);

        $facture = FactureAchat::create(array_merge($data, [
            'numero'      => $this->numerotation->suivant('facture_achat'),
            'created_by'  => auth()->id(),
            'montant_tva' => round($tva, 2),
            'montant_ttc' => round($ht + $tva, 2),
        ]));

        return redirect()->route('factures-achat.show', $facture)
            ->with('success', "Facture achat {$facture->numero} enregistrée.");
    }

    public function show(FactureAchat $facturesAchat)
    {
        $facturesAchat->load('fournisseur', 'chantier', 'bonCommande');
        return view('factures-achat.show', ['facture' => $facturesAchat]);
    }

    public function edit(FactureAchat $facturesAchat)
    {
        $facture      = $facturesAchat;
        $fournisseurs = Fournisseur::actif()->orderBy('nom')->get();
        $chantiers    = Chantier::with('client')->orderByDesc('id')->get();
        $bonsCommande = BonCommande::with('client')->orderByDesc('id')->take(50)->get();

        return view('factures-achat.edit', compact('facture', 'fournisseurs', 'chantiers', 'bonsCommande'));
    }

    public function update(Request $request, FactureAchat $facturesAchat)
    {
        $data = $request->validate([
            'fournisseur_id'         => 'required|exists:fournisseurs,id',
            'chantier_id'            => 'nullable|exists:chantiers,id',
            'bon_commande_id'        => 'nullable|exists:bons_commande,id',
            'reference_fournisseur'  => 'nullable|string|max:60',
            'categorie'              => 'required|in:materiel,sous_traitance,frais_generaux,divers',
            'date_document'          => 'required|date',
            'date_echeance'          => 'nullable|date',
            'montant_ht'             => 'required|numeric|min:0',
            'taux_tva'               => 'required|numeric|min:0|max:100',
            'statut'                 => 'required|in:en_attente,payee',
            'date_paiement'          => 'nullable|date',
            'notes'                  => 'nullable|string',
        ]);

        $ht  = (float) $data['montant_ht'];
        $tva = $ht * ($data['taux_tva'] / 100);

        $facturesAchat->update(array_merge($data, [
            'montant_tva' => round($tva, 2),
            'montant_ttc' => round($ht + $tva, 2),
        ]));

        return redirect()->route('factures-achat.show', $facturesAchat)
            ->with('success', 'Facture achat mise à jour.');
    }

    public function destroy(FactureAchat $facturesAchat)
    {
        $facturesAchat->delete();
        return redirect()->route('factures-achat.index')->with('success', 'Facture achat supprimée.');
    }

    public function marquerPayee(Request $request, FactureAchat $facturesAchat)
    {
        $data = $request->validate(['date_paiement' => 'required|date']);
        $facturesAchat->update(['statut' => 'payee', 'date_paiement' => $data['date_paiement']]);
        return redirect()->route('factures-achat.show', $facturesAchat)
            ->with('success', 'Facture marquée comme payée.');
    }
}
