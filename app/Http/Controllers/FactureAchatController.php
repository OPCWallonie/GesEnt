<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Chantier;
use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $fournisseurId       = $request->fournisseur_id ?: old('fournisseur_id');
        $chantierId          = $request->chantier_id;
        $fournisseurSelectionne = $fournisseurId ? Fournisseur::find($fournisseurId) : null;

        return view('factures-achat.create', compact('fournisseurs', 'chantiers', 'bonsCommande', 'fournisseurId', 'chantierId', 'fournisseurSelectionne'));
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
            'numero'        => $this->numerotation->suivant('facture_achat'),
            'created_by'    => auth()->id(),
            'montant_tva'   => round($tva, 2),
            'montant_ttc'   => round($ht + $tva, 2),
            'peppol_source' => $request->boolean('from_ocr') ? 'ocr' : 'manuel',
        ]));

        if ($request->hasFile('fichier_original')) {
            $file = $request->file('fichier_original');
            $path = $file->store('factures-achat/' . now()->format('Y/m'), 'local');
            $facture->update([
                'fichier_path'         => $path,
                'fichier_mime'         => $file->getMimeType(),
                'fichier_nom_original' => $file->getClientOriginalName(),
            ]);
        }

        return redirect()->route('factures-achat.show', $facture)
            ->with('success', "Facture achat {$facture->numero} enregistrée.");
    }

    public function show(FactureAchat $factureAchat)
    {
        $factureAchat->load('fournisseur', 'chantier', 'bonCommande');
        return view('factures-achat.show', ['facture' => $factureAchat]);
    }

    public function edit(FactureAchat $factureAchat)
    {
        if (! $factureAchat->peutEtreModifie()) {
            return redirect()->route('factures-achat.show', $factureAchat)
                ->with('error', 'Cette facture est archivée et ne peut pas être modifiée.');
        }

        $facture      = $factureAchat;
        $fournisseurs = Fournisseur::actif()->orderBy('nom')->get();
        $chantiers    = Chantier::with('client')->orderByDesc('id')->get();
        $bonsCommande = BonCommande::with('client')->orderByDesc('id')->take(50)->get();

        return view('factures-achat.edit', compact('facture', 'fournisseurs', 'chantiers', 'bonsCommande'));
    }

    public function update(Request $request, FactureAchat $factureAchat)
    {
        if (! $factureAchat->peutEtreModifie()) {
            return redirect()->route('factures-achat.show', $factureAchat)
                ->with('error', 'Cette facture est archivée et ne peut pas être modifiée.');
        }

        $champsEditables = $factureAchat->champsEditables();

        $rules = [
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
        ];

        // For non-manual invoices only validate editable fields
        if (! $factureAchat->estManuelle()) {
            $rules = array_intersect_key($rules, array_flip($champsEditables));
        }

        $data = $request->validate($rules);

        // For non-manual invoices, merge existing values for non-editable fields
        if (! $factureAchat->estManuelle()) {
            $ht  = (float) $factureAchat->montant_ht;
            $tva = (float) $factureAchat->montant_tva;
            $factureAchat->update($data);
            return redirect()->route('factures-achat.show', $factureAchat)
                ->with('success', 'Facture achat mise à jour.');
        }

        $ht  = (float) $data['montant_ht'];
        $tva = $ht * ($data['taux_tva'] / 100);

        $factureAchat->update(array_merge($data, [
            'montant_tva' => round($tva, 2),
            'montant_ttc' => round($ht + $tva, 2),
        ]));

        if ($request->hasFile('fichier_original')) {
            if ($factureAchat->fichier_path) {
                Storage::disk('local')->delete($factureAchat->fichier_path);
            }
            $file = $request->file('fichier_original');
            $path = $file->store('factures-achat/' . now()->format('Y/m'), 'local');
            $factureAchat->update([
                'fichier_path'         => $path,
                'fichier_mime'         => $file->getMimeType(),
                'fichier_nom_original' => $file->getClientOriginalName(),
            ]);
        }

        return redirect()->route('factures-achat.show', $factureAchat)
            ->with('success', 'Facture achat mise à jour.');
    }

    public function archiver(FactureAchat $factureAchat)
    {
        if (! $factureAchat->peutEtreArchive()) {
            return back()->with('error', 'Cette facture est déjà archivée.');
        }
        $factureAchat->update(['statut' => 'archive']);
        return redirect()->route('factures-achat.show', $factureAchat)->with('success', 'Facture achat archivée.');
    }

    public function destroy(FactureAchat $factureAchat)
    {
        if (! $factureAchat->peutEtreSupprime()) {
            return back()->with('error', 'Cette facture ne peut pas être supprimée (payée ou archivée).');
        }
        $factureAchat->delete();
        return redirect()->route('factures-achat.index')->with('success', 'Facture achat supprimée.');
    }

    public function fichier(FactureAchat $factureAchat)
    {
        if (! $factureAchat->fichier_path || ! Storage::disk('local')->exists($factureAchat->fichier_path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return Storage::disk('local')->response(
            $factureAchat->fichier_path,
            $factureAchat->fichier_nom_original,
            ['Content-Type' => $factureAchat->fichier_mime ?? 'application/pdf']
        );
    }

    public function marquerPayee(Request $request, FactureAchat $factureAchat)
    {
        $data = $request->validate(['date_paiement' => 'required|date']);
        $factureAchat->update(['statut' => 'payee', 'date_paiement' => $data['date_paiement']]);
        return redirect()->route('factures-achat.show', $factureAchat)
            ->with('success', 'Facture marquée comme payée.');
    }
}
