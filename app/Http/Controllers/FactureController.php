<?php

namespace App\Http\Controllers;

use App\Mail\FactureEnvoyee;
use App\Models\BonCommande;
use App\Models\Facture;
use App\Models\ModePaiement;
use App\Models\ParametresEntreprise;
use App\Models\TauxTva;
use App\Services\NumerotationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FactureController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function index(Request $request)
    {
        $factures = Facture::with('client', 'chantier', 'bonCommande')
            ->when($request->q, fn($q, $s) => $q->where('numero', 'like', "%{$s}%")
                ->orWhereHas('client', fn($q) => $q->where('nom', 'like', "%{$s}%")))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->orderByDesc('date_document')
            ->paginate(20)
            ->withQueryString();

        return view('factures.index', compact('factures'));
    }

    public function create(Request $request)
    {
        $bdcSource = $request->bon_commande_id
            ? BonCommande::with('client', 'chantier', 'modePaiement', 'avenants')->find($request->bon_commande_id)
            : null;

        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();

        // Pré-calculer les totaux depuis le BDC + avenants
        $totauxBdc = $bdcSource ? $bdcSource->montantTotalAvecAvenants() : null;

        return view('factures.create', compact('bdcSource', 'modesPaiement', 'tauxTva', 'totauxBdc'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bon_commande_id'      => 'nullable|exists:bons_commande,id',
            'mode_paiement_id'     => 'nullable|exists:modes_paiement,id',
            'statut'               => 'required|in:en_attente,envoyee',
            'date_document'        => 'required|date',
            'date_echeance'        => 'nullable|date',
            'frais_port'           => 'nullable|numeric|min:0',
            'ristourne_globale'    => 'nullable|numeric|min:0|max:100',
            'acompte_deduit'       => 'nullable|numeric|min:0',
            'retenue_garantie_pct' => 'nullable|numeric|min:0|max:100',
            'delai_reglement'      => 'nullable|integer|min:0',
            'notes'                => 'nullable|string',
            'lignes'           => 'required|array|min:1',
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

        $facture = DB::transaction(function () use ($data) {
            $bdc = $data['bon_commande_id'] ? BonCommande::find($data['bon_commande_id']) : null;

            $facture = Facture::create([
                'numero'            => $this->numerotation->suivant('facture'),
                'bon_commande_id'   => $bdc?->id,
                'client_id'         => $bdc?->client_id ?? request('client_id'),
                'chantier_id'       => $bdc?->chantier_id,
                'mode_paiement_id'  => $data['mode_paiement_id'] ?? $bdc?->mode_paiement_id,
                'created_by'        => auth()->id(),
                'statut'               => $data['statut'],
                'date_document'        => $data['date_document'],
                'date_echeance'        => $data['date_echeance'] ?? null,
                'frais_port'           => $data['frais_port'] ?? 0,
                'ristourne_globale'    => $data['ristourne_globale'] ?? 0,
                'acompte_deduit'       => $data['acompte_deduit'] ?? 0,
                'retenue_garantie_pct' => $data['retenue_garantie_pct'] ?? 0,
                'delai_reglement'      => $data['delai_reglement'] ?? 30,
                'notes'                => $data['notes'] ?? null,
            ]);

            $this->enregistrerLignes($facture, $data['lignes']);
            $this->recalculerMontants($facture);
            return $facture;
        });

        return redirect()->route('factures.show', $facture)
            ->with('success', "Facture {$facture->numero} créée.");
    }

    public function show(Facture $facture)
    {
        $facture->load('client', 'chantier', 'modePaiement', 'lignes', 'bonCommande', 'avoirs');
        $parametres = ParametresEntreprise::instance();
        $totauxTva  = DevisController::calculerTotauxTva($facture->lignes);

        return view('factures.show', compact('facture', 'parametres', 'totauxTva'));
    }

    public function edit(Facture $facture)
    {
        if ($facture->statut === 'payee') {
            return redirect()->route('factures.show', $facture)->with('error', 'Une facture payée ne peut plus être modifiée.');
        }

        $facture->load('lignes');
        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();

        return view('factures.edit', compact('facture', 'modesPaiement', 'tauxTva'));
    }

    public function update(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'mode_paiement_id'     => 'nullable|exists:modes_paiement,id',
            'statut'               => 'required|in:en_attente,envoyee,payee,en_retard,archive',
            'date_document'        => 'required|date',
            'date_echeance'        => 'nullable|date',
            'frais_port'           => 'nullable|numeric|min:0',
            'ristourne_globale'    => 'nullable|numeric|min:0|max:100',
            'acompte_deduit'       => 'nullable|numeric|min:0',
            'retenue_garantie_pct' => 'nullable|numeric|min:0|max:100',
            'delai_reglement'      => 'nullable|integer|min:0',
            'date_paiement'        => 'nullable|date',
            'montant_paye'         => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string',
            'lignes'           => 'required|array|min:1',
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

        DB::transaction(function () use ($facture, $data) {
            $facture->update([
                'mode_paiement_id'     => $data['mode_paiement_id'] ?? null,
                'statut'               => $data['statut'],
                'date_document'        => $data['date_document'],
                'date_echeance'        => $data['date_echeance'] ?? null,
                'frais_port'           => $data['frais_port'] ?? 0,
                'ristourne_globale'    => $data['ristourne_globale'] ?? 0,
                'acompte_deduit'       => $data['acompte_deduit'] ?? 0,
                'retenue_garantie_pct' => $data['retenue_garantie_pct'] ?? 0,
                'delai_reglement'      => $data['delai_reglement'] ?? 30,
                'date_paiement'        => $data['date_paiement'] ?? null,
                'montant_paye'         => $data['montant_paye'] ?? 0,
                'notes'                => $data['notes'] ?? null,
            ]);
            $facture->lignes()->delete();
            $this->enregistrerLignes($facture, $data['lignes']);
            $this->recalculerMontants($facture);
        });

        return redirect()->route('factures.show', $facture)->with('success', 'Facture mise à jour.');
    }

    public function destroy(Facture $facture)
    {
        if ($facture->statut === 'payee') {
            return back()->with('error', 'Impossible de supprimer une facture payée.');
        }
        DB::transaction(function () use ($facture) {
            $facture->lignes()->delete();
            $facture->delete();
        });
        return redirect()->route('factures.index')->with('success', "Facture {$facture->numero} supprimée.");
    }

    public function marquerPayee(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'date_paiement' => 'required|date',
            'montant_paye'  => 'required|numeric|min:0',
        ]);

        $facture->update([
            'statut'        => 'payee',
            'date_paiement' => $data['date_paiement'],
            'montant_paye'  => $data['montant_paye'],
        ]);

        return redirect()->route('factures.show', $facture)
            ->with('success', "Facture {$facture->numero} marquée comme payée.");
    }

    public function envoyer(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'email'   => 'required|email',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            Mail::to($data['email'])->send(new FactureEnvoyee($facture, $data['message'] ?? ''));

            if ($facture->statut === 'en_attente') {
                $facture->update(['statut' => 'envoyee']);
            }

            return back()->with('success', "Facture {$facture->numero} envoyée à {$data['email']}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur d\'envoi : ' . $e->getMessage() . '. Vérifiez la configuration SMTP dans le fichier .env.');
        }
    }

    public function relancer(Facture $facture)
    {
        if ($facture->statut === 'payee') {
            return back()->with('error', 'Cette facture est déjà payée.');
        }
        $facture->enregistrerRelance();
        return back()->with('success', "Relance n°{$facture->nb_relances} enregistrée pour {$facture->numero}.");
    }

    public function libererRetenue(Facture $facture)
    {
        if ($facture->retenue_garantie_pct <= 0) {
            return back()->with('error', 'Cette facture n\'a pas de retenue de garantie.');
        }
        if ($facture->retenue_garantie_liberee_at) {
            return back()->with('error', 'La retenue de garantie a déjà été libérée.');
        }

        $facture->update(['retenue_garantie_liberee_at' => now()]);

        return back()->with('success',
            "Retenue de garantie de {$facture->numero} libérée — " .
            number_format($facture->retenue_garantie_montant, 2, ',', ' ') . " € à encaisser."
        );
    }

    public function pdf(Facture $facture)
    {
        $facture->load('client', 'chantier', 'modePaiement', 'lignes', 'bonCommande');
        $parametres = ParametresEntreprise::instance();
        $totauxTva  = DevisController::calculerTotauxTva($facture->lignes);

        $pdf = Pdf::loadView('pdf.facture', compact('facture', 'parametres', 'totauxTva'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("facture-{$facture->numero}.pdf");
    }

    private function enregistrerLignes($facture, array $lignes): void
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

            $facture->lignes()->create([
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

    private function recalculerMontants(Facture $facture): void
    {
        $lignes = $facture->lignes;
        $ht  = $lignes->where('est_section', false)->sum('montant_ht');
        $tva = $lignes->where('est_section', false)->sum(
            fn($l) => $l->montant_ht * ($l->taux_tva / 100)
        );
        $ristourne = $ht * ($facture->ristourne_globale / 100);
        $htNet     = $ht - $ristourne + $facture->frais_port;
        $tvaNet    = $tva * (1 - $facture->ristourne_globale / 100);
        $ttc       = $htNet + $tvaNet;
        $base      = max(0, $ttc - $facture->acompte_deduit);
        $retenue   = $base * ($facture->retenue_garantie_pct / 100);
        $netAPayer = max(0, $base - $retenue);

        $facture->update([
            'montant_ht'               => $htNet,
            'montant_tva'              => $tvaNet,
            'montant_ttc'              => $ttc,
            'retenue_garantie_montant' => $retenue,
            'montant_net_a_payer'      => $netAPayer,
        ]);
    }
}
