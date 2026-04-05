<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Client;
use App\Models\Devis;
use App\Models\ModePaiement;
use App\Models\ParametresEntreprise;
use App\Models\TauxTva;
use App\Services\NumerotationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonCommandeController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function index(Request $request)
    {
        $bons = BonCommande::with('client', 'chantier', 'devis')
            ->when($request->q, fn($q, $s) => $q->where('numero', 'like', "%{$s}%")
                ->orWhereHas('client', fn($q) => $q->where('nom', 'like', "%{$s}%")))
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

            $this->enregistrerLignes($bdc, $data['lignes']);
            $this->recalculerMontants($bdc);
            return $bdc;
        });

        return redirect()->route('bons-commande.show', $bdc)
            ->with('success', "Bon de commande {$bdc->numero} créé.");
    }

    public function show(BonCommande $bonsCommande)
    {
        $bonsCommande->load('client', 'chantier', 'modePaiement', 'lignes', 'avenants.lignes', 'facture', 'devis');
        $parametres = ParametresEntreprise::instance();
        $totaux     = $bonsCommande->montantTotalAvecAvenants();

        return view('bons-commande.show', [
            'bdc'        => $bonsCommande,
            'parametres' => $parametres,
            'totaux'     => $totaux,
        ]);
    }

    public function edit(BonCommande $bonsCommande)
    {
        if ($bonsCommande->facture) {
            return redirect()->route('bons-commande.show', $bonsCommande)
                ->with('error', 'Ce BDC est déjà facturé.');
        }

        $bonsCommande->load('lignes');
        $clients       = Client::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();
        $chantiers     = $bonsCommande->client->chantiers()->where('statut', 'actif')->get(['id', 'nom']);

        return view('bons-commande.edit', ['bdc' => $bonsCommande, 'clients' => $clients, 'modesPaiement' => $modesPaiement, 'tauxTva' => $tauxTva, 'chantiers' => $chantiers]);
    }

    public function update(Request $request, BonCommande $bonsCommande)
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

        DB::transaction(function () use ($bonsCommande, $data) {
            $bonsCommande->update([
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
            $bonsCommande->lignes()->delete();
            $this->enregistrerLignes($bonsCommande, $data['lignes']);
            $this->recalculerMontants($bonsCommande);
        });

        return redirect()->route('bons-commande.show', $bonsCommande)->with('success', 'BDC mis à jour.');
    }

    public function destroy(BonCommande $bonsCommande)
    {
        if ($bonsCommande->facture) {
            return back()->with('error', 'Impossible de supprimer un BDC facturé.');
        }
        DB::transaction(function () use ($bonsCommande) {
            foreach ($bonsCommande->avenants as $avenant) {
                $avenant->lignes()->delete();
                $avenant->delete();
            }
            $bonsCommande->lignes()->delete();
            $bonsCommande->delete();
        });
        return redirect()->route('bons-commande.index')->with('success', "BDC {$bonsCommande->numero} supprimé.");
    }

    public function facturer(BonCommande $bonsCommande)
    {
        if (! $bonsCommande->peutEtreFacture()) {
            return back()->with('error', 'Le BDC et tous ses avenants doivent être validés avant facturation.');
        }
        if ($bonsCommande->facture) {
            return redirect()->route('factures.show', $bonsCommande->facture)
                ->with('error', 'Ce BDC est déjà facturé.');
        }
        return redirect()->route('factures.create', ['bon_commande_id' => $bonsCommande->id]);
    }

    public function pdf(BonCommande $bonsCommande)
    {
        $bonsCommande->load('client', 'chantier', 'modePaiement', 'lignes', 'avenants.lignes');
        $parametres = ParametresEntreprise::instance();
        $totaux     = $bonsCommande->montantTotalAvecAvenants();
        $totauxTva  = DevisController::calculerTotauxTva($bonsCommande->toutesLesLignes());

        $pdf = Pdf::loadView('pdf.bon-commande', compact('bonsCommande', 'parametres', 'totaux', 'totauxTva'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("bdc-{$bonsCommande->numero}.pdf");
    }

    private function enregistrerLignes($document, array $lignes): void
    {
        foreach ($lignes as $ordre => $ligneData) {
            $estSection = ! empty($ligneData['est_section']);
            $montantHt  = 0;

            if (! $estSection) {
                $brut   = (float)($ligneData['prix_unitaire'] ?? 0) * (float)($ligneData['quantite'] ?? 1);
                $remise = ($ligneData['remise_type'] ?? 'montant') === 'pourcentage'
                    ? $brut * ((float)($ligneData['remise_valeur'] ?? 0) / 100)
                    : (float)($ligneData['remise_valeur'] ?? 0);
                $montantHt = max(0, $brut - $remise);
            }

            $document->lignes()->create([
                'ordre'          => $ordre,
                'est_section'    => $estSection,
                'designation'    => $ligneData['designation'],
                'detail'         => $ligneData['detail'] ?? null,
                'unite'          => $ligneData['unite'] ?? 'pièce',
                'quantite'       => $ligneData['quantite'] ?? 1,
                'prix_unitaire'  => $ligneData['prix_unitaire'] ?? 0,
                'remise_valeur'  => $ligneData['remise_valeur'] ?? 0,
                'remise_type'    => $ligneData['remise_type'] ?? 'montant',
                'taux_tva'       => $ligneData['taux_tva'] ?? 21,
                'montant_ht'     => $montantHt,
            ]);
        }
    }

    private function recalculerMontants(BonCommande $bdc): void
    {
        $lignes = $bdc->lignes;
        $ht = $lignes->where('est_section', false)->sum('montant_ht');
        $tva = $lignes->where('est_section', false)->sum(
            fn($l) => $l->montant_ht * ($l->taux_tva / 100)
        );
        $ristourne = $ht * ($bdc->ristourne_globale / 100);
        $htNet = $ht - $ristourne + $bdc->frais_port;
        $tvaNet = $tva * (1 - $bdc->ristourne_globale / 100);

        $bdc->update([
            'montant_ht'  => $htNet,
            'montant_tva' => $tvaNet,
            'montant_ttc' => $htNet + $tvaNet,
        ]);
    }
}
