<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Client;
use App\Models\Devis;
use App\Models\ModePaiement;
use App\Models\ParametresEntreprise;
use App\Models\TauxTva;
use App\Services\DocumentService;
use App\Services\NumerotationService;
use App\Services\ProduitUsageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonCommandeController extends Controller
{
    public function __construct(
        private NumerotationService $numerotation,
        private DocumentService $documentService,
        private ProduitUsageService $usageService,
    ) {}

    public function index(Request $request)
    {
        $bons = BonCommande::with('client', 'chantier', 'devis')
            ->when($request->q, fn($q, $s) => $q->where('numero', 'like', '%' . like_escape($s) . '%')
                ->orWhereHas('client', fn($q) => $q->where('nom', 'like', '%' . like_escape($s) . '%')))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->orderByDesc('date_document')
            ->paginate(20)
            ->withQueryString();

        $bonsCommande = $bons;
        return view('bons-commande.index', compact('bonsCommande'));
    }

    public function create(Request $request)
    {
        $devisSource = $request->devis_id ? Devis::with('client', 'chantier', 'lignes', 'modePaiement')->find($request->devis_id) : null;
        $clients     = Client::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva     = TauxTva::orderBy('taux')->get();

        $chantiers = $devisSource?->client
            ? $devisSource->client->chantiers()->where('statut', 'actif')->get(['id', 'nom'])
            : collect();

        return view('bons-commande.create', compact('devisSource', 'clients', 'modesPaiement', 'tauxTva', 'chantiers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'devis_id'               => 'nullable|exists:devis,id',
            'client_id'              => 'required|exists:clients,id',
            'chantier_id'            => 'nullable|exists:chantiers,id',
            'mode_paiement_id'       => 'nullable|exists:modes_paiement,id',
            'statut'                 => 'required|in:en_attente,valide',
            'date_document'          => 'required|date',
            'date_debut_travaux'     => 'nullable|date',
            'date_fin_prevue'        => 'nullable|date',
            'frais_port'             => 'nullable|numeric|min:0',
            'ristourne_globale'      => 'nullable|numeric|min:0|max:100',
            'acompte'                => 'nullable|numeric|min:0',
            'delai_reglement'        => 'nullable|integer|min:0',
            'notes'                  => 'nullable|string',
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

        $bdc = DB::transaction(function () use ($data) {
            $bdc = BonCommande::create([
                'numero'             => $this->numerotation->suivant('bon_commande'),
                'devis_id'           => $data['devis_id'] ?? null,
                'client_id'          => $data['client_id'],
                'chantier_id'        => $data['chantier_id'] ?? null,
                'mode_paiement_id'   => $data['mode_paiement_id'] ?? null,
                'created_by'         => auth()->id(),
                'statut'             => $data['statut'],
                'date_document'      => $data['date_document'],
                'date_debut_travaux' => $data['date_debut_travaux'] ?? null,
                'date_fin_prevue'    => $data['date_fin_prevue'] ?? null,
                'frais_port'         => $data['frais_port'] ?? 0,
                'ristourne_globale'  => $data['ristourne_globale'] ?? 0,
                'acompte'            => $data['acompte'] ?? 0,
                'delai_reglement'    => $data['delai_reglement'] ?? 30,
                'notes'              => $data['notes'] ?? null,
            ]);

            $this->documentService->enregistrerLignes($bdc, $data['lignes']);
            $this->documentService->recalculerMontants($bdc);
            $this->usageService->enregistrerUtilisation($bdc);
            return $bdc;
        });

        return redirect()->route('bons-commande.show', $bdc)
            ->with('success', "Bon de commande {$bdc->numero} créé.");
    }

    public function show(BonCommande $bonCommande)
    {
        $bonCommande->load('client', 'chantier', 'modePaiement', 'lignes', 'avenants.lignes', 'factures', 'devis');
        $parametres = ParametresEntreprise::instance();
        $totaux     = $bonCommande->montantTotalAvecAvenants();

        return view('bons-commande.show', [
            'bdc'        => $bonCommande,
            'parametres' => $parametres,
            'totaux'     => $totaux,
        ]);
    }

    public function edit(BonCommande $bonCommande)
    {
        if ($bonCommande->factures->isNotEmpty()) {
            return redirect()->route('bons-commande.show', $bonCommande)
                ->with('error', 'Ce BDC est déjà facturé.');
        }

        $bonCommande->load('lignes');
        $clients       = Client::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();
        $chantiers     = $bonCommande->client->chantiers()->where('statut', 'actif')->get(['id', 'nom']);

        return view('bons-commande.edit', ['bdc' => $bonCommande, 'clients' => $clients, 'modesPaiement' => $modesPaiement, 'tauxTva' => $tauxTva, 'chantiers' => $chantiers]);
    }

    public function update(Request $request, BonCommande $bonCommande)
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'chantier_id'            => 'nullable|exists:chantiers,id',
            'mode_paiement_id'       => 'nullable|exists:modes_paiement,id',
            'statut'                 => 'required|in:en_attente,valide,en_cours,termine,archive',
            'date_document'          => 'required|date',
            'date_debut_travaux'     => 'nullable|date',
            'date_fin_prevue'        => 'nullable|date',
            'frais_port'             => 'nullable|numeric|min:0',
            'ristourne_globale'      => 'nullable|numeric|min:0|max:100',
            'acompte'                => 'nullable|numeric|min:0',
            'delai_reglement'        => 'nullable|integer|min:0',
            'notes'                  => 'nullable|string',
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

        DB::transaction(function () use ($bonCommande, $data) {
            $bonCommande->update([
                'client_id'          => $data['client_id'],
                'chantier_id'        => $data['chantier_id'] ?? null,
                'mode_paiement_id'   => $data['mode_paiement_id'] ?? null,
                'statut'             => $data['statut'],
                'date_document'      => $data['date_document'],
                'date_debut_travaux' => $data['date_debut_travaux'] ?? null,
                'date_fin_prevue'    => $data['date_fin_prevue'] ?? null,
                'frais_port'         => $data['frais_port'] ?? 0,
                'ristourne_globale'  => $data['ristourne_globale'] ?? 0,
                'acompte'            => $data['acompte'] ?? 0,
                'delai_reglement'    => $data['delai_reglement'] ?? 30,
                'notes'              => $data['notes'] ?? null,
            ]);
            $bonCommande->lignes()->delete();
            $this->documentService->enregistrerLignes($bonCommande, $data['lignes']);
            $this->documentService->recalculerMontants($bonCommande);
        });

        return redirect()->route('bons-commande.show', $bonCommande)->with('success', 'BDC mis à jour.');
    }

    public function destroy(BonCommande $bonCommande)
    {
        if ($bonCommande->factures->isNotEmpty()) {
            return back()->with('error', 'Impossible de supprimer un BDC facturé.');
        }
        DB::transaction(function () use ($bonCommande) {
            foreach ($bonCommande->avenants as $avenant) {
                $avenant->lignes()->delete();
                $avenant->delete();
            }
            $bonCommande->lignes()->delete();
            $bonCommande->delete();
        });
        return redirect()->route('bons-commande.index')->with('success', "BDC {$bonCommande->numero} supprimé.");
    }

    public function facturer(BonCommande $bonCommande)
    {
        if (! $bonCommande->peutEtreFacture()) {
            return back()->with('error', 'Le BDC doit être validé, ses avenants aussi, et il doit rester du montant à facturer.');
        }

        return redirect()->route('factures.create', [
            'bon_commande_id' => $bonCommande->id,
            'situation'       => $bonCommande->prochainNumeroSituation(),
        ]);
    }

    public function pdf(BonCommande $bonCommande)
    {
        $bonCommande->load('client', 'chantier', 'modePaiement', 'lignes', 'avenants.lignes');
        $parametres = ParametresEntreprise::instance();
        $totaux     = $bonCommande->montantTotalAvecAvenants();
        $totauxTva  = $this->documentService->calculerTotauxTva($bonCommande->toutesLesLignes());

        $pdf = Pdf::loadView('pdf.bon-commande', compact('bonCommande', 'parametres', 'totaux', 'totauxTva'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("bdc-{$bonCommande->numero}.pdf");
    }

}
