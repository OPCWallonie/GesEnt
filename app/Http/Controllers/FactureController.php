<?php

namespace App\Http\Controllers;

use App\Mail\FactureEnvoyee;
use App\Models\BonCommande;
use App\Models\Facture;
use App\Models\ModePaiement;
use App\Models\ParametresEntreprise;
use App\Models\TauxTva;
use App\Models\Paiement;
use App\Services\DocumentService;
use App\Services\NumerotationService;
use App\States\Facture\Archive as FactureArchive;
use App\States\Facture\EnAttente;
use App\States\Facture\EnRetard;
use App\States\Facture\Envoyee;
use App\States\Facture\Payee;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FactureController extends Controller
{
    public function __construct(
        private NumerotationService $numerotation,
        private DocumentService $documentService,
    ) {}

    public function index(Request $request)
    {
        $factures = Facture::with('client', 'chantier', 'bonCommande')
            ->when($request->q, fn($q, $s) => $q->where('numero', 'like', '%' . like_escape($s) . '%')
                ->orWhereHas('client', fn($q) => $q->where('nom', 'like', '%' . like_escape($s) . '%')))
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
            ? BonCommande::with('client', 'chantier', 'modePaiement', 'avenants', 'factures')->find($request->bon_commande_id)
            : null;

        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();

        // Pré-calculer les totaux depuis le BDC + avenants
        $totauxBdc = $bdcSource ? $bdcSource->montantTotalAvecAvenants() : null;

        // Informations de situation pour la facturation partielle
        $infoSituation = null;
        if ($bdcSource) {
            $totaux        = $bdcSource->montantTotalAvecAvenants();
            $infoSituation = [
                'numero_situation'     => $bdcSource->prochainNumeroSituation(),
                'pct_deja_facture'     => $bdcSource->pourcentageFacture(),
                'pct_restant'          => $bdcSource->pourcentageRestant(),
                'montant_total_bdc'    => $totaux['ttc'],
                'montant_deja_facture' => $bdcSource->montantFacture(),
                'montant_restant'      => $bdcSource->montantRestant(),
                'factures_precedentes' => $bdcSource->factures,
            ];
        }

        return view('factures.create', compact('bdcSource', 'modesPaiement', 'tauxTva', 'totauxBdc', 'infoSituation'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bon_commande_id'        => 'nullable|exists:bons_commande,id',
            'mode_paiement_id'       => 'nullable|exists:modes_paiement,id',
            'statut'                 => 'required|in:en_attente,envoyee',
            'date_document'          => 'required|date',
            'date_echeance'          => 'nullable|date',
            'frais_port'             => 'nullable|numeric|min:0',
            'ristourne_globale'      => 'nullable|numeric|min:0|max:100',
            'acompte_deduit'         => 'nullable|numeric|min:0',
            'retenue_garantie_pct'   => 'nullable|numeric|min:0|max:100',
            'delai_reglement'        => 'nullable|integer|min:0',
            'notes'                  => 'nullable|string',
            'numero_situation'       => 'nullable|integer|min:1',
            'pourcentage_avancement' => 'nullable|numeric|min:0|max:100',
            'pourcentage_cumule'     => 'nullable|numeric|min:0|max:100',
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

            $this->documentService->enregistrerLignes($facture, $data['lignes']);
            $this->documentService->recalculerMontants($facture);

            // Champs de situation (facturation partielle)
            if (!empty($data['numero_situation'])) {
                $facture->update([
                    'numero_situation'       => $data['numero_situation'],
                    'pourcentage_avancement' => $data['pourcentage_avancement'] ?? null,
                    'pourcentage_cumule'     => $data['pourcentage_cumule'] ?? null,
                    'montant_anterieur'      => $bdc ? $bdc->montantFacture() - $facture->montant_ttc : 0,
                ]);
            }

            return $facture;
        });

        return redirect()->route('factures.show', $facture)
            ->with('success', "Facture {$facture->numero} créée.");
    }

    public function show(Facture $facture)
    {
        $facture->load('client', 'chantier', 'modePaiement', 'lignes', 'bonCommande', 'avoirs', 'paiements');
        $parametres = ParametresEntreprise::instance();
        $totauxTva  = $this->documentService->calculerTotauxTva($facture->lignes);

        return view('factures.show', compact('facture', 'parametres', 'totauxTva'));
    }

    public function edit(Facture $facture)
    {
        if ($facture->statut->is(Payee::class)) {
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

        $nouveauStatut = $data['statut'];
        $ancienStatut  = (string) $facture->statut;

        try {
            DB::transaction(function () use ($facture, $data, $nouveauStatut, $ancienStatut) {
                $facture->update([
                    'mode_paiement_id'     => $data['mode_paiement_id'] ?? null,
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
                $this->documentService->enregistrerLignes($facture, $data['lignes']);
                $this->documentService->recalculerMontants($facture);

                if ($nouveauStatut !== $ancienStatut) {
                    $stateClass = match ($nouveauStatut) {
                        'en_attente' => EnAttente::class,
                        'envoyee'    => Envoyee::class,
                        'en_retard'  => EnRetard::class,
                        'payee'      => Payee::class,
                        'archive'    => FactureArchive::class,
                        default      => null,
                    };
                    if ($stateClass) {
                        $facture->statut->transitionTo($stateClass);
                    }
                }
            });
        } catch (TransitionNotFound $e) {
            return redirect()->route('factures.edit', $facture)
                ->with('error', "Transition de statut impossible : {$ancienStatut} → {$nouveauStatut}.");
        }

        return redirect()->route('factures.show', $facture)->with('success', 'Facture mise à jour.');
    }

    public function destroy(Facture $facture)
    {
        if ($facture->statut->is(Payee::class)) {
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
            'montant_paye'  => 'required|numeric|min:0.01',
            'mode'          => 'nullable|string|max:50',
            'reference'     => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);

        Paiement::create([
            'facture_id'    => $facture->id,
            'created_by'    => auth()->id(),
            'date_paiement' => $data['date_paiement'],
            'montant'       => $data['montant_paye'],
            'mode'          => $data['mode'] ?? null,
            'reference'     => $data['reference'] ?? null,
            'notes'         => $data['notes'] ?? null,
        ]);

        $facture->recalculerPaiements();

        $message = $facture->est_totalement_payee
            ? "Facture {$facture->numero} entièrement payée."
            : "Paiement de " . number_format($data['montant_paye'], 2, ',', ' ') . " € enregistré pour {$facture->numero}.";

        return redirect()->route('factures.show', $facture)->with('success', $message);
    }

    public function envoyer(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'email'   => 'required|email',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            Mail::to($data['email'])->send(new FactureEnvoyee($facture, $data['message'] ?? ''));

            if ($facture->statut->is(EnAttente::class)) {
                $facture->statut->transitionTo(Envoyee::class);
            }

            return back()->with('success', "Facture {$facture->numero} envoyée à {$data['email']}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur d\'envoi : ' . $e->getMessage() . '. Vérifiez la configuration SMTP dans le fichier .env.');
        }
    }

    public function relancer(Facture $facture)
    {
        if ($facture->statut->is(Payee::class)) {
            return back()->with('error', 'Cette facture est déjà payée.');
        }
        $facture->enregistrerRelance();
        return back()->with('success', "Relance n°{$facture->nb_relances} enregistrée pour {$facture->numero}.");
    }

    public function toggleRelanceAuto(Request $request, Facture $facture)
    {
        $facture->update(['relance_auto' => $request->boolean('relance_auto')]);

        $message = $facture->relance_auto
            ? 'Relance automatique activée.'
            : 'Relance automatique désactivée pour cette facture.';

        return back()->with('success', $message);
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
        $totauxTva  = $this->documentService->calculerTotauxTva($facture->lignes);

        $pdf = Pdf::loadView('pdf.facture', compact('facture', 'parametres', 'totauxTva'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("facture-{$facture->numero}.pdf");
    }

}
