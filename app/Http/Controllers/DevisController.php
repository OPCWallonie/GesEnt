<?php

namespace App\Http\Controllers;

use App\Mail\DevisEnvoye;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\Devis;
use App\Models\ModePaiement;
use App\Models\ParametresEntreprise;
use App\Models\TauxTva;
use App\Services\NumerotationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DevisController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function index(Request $request)
    {
        $devis = Devis::with('client', 'chantier')
            ->when($request->q, fn($q, $s) => $q->whereHas('client', fn($q) => $q->where('nom', 'like', "%{$s}%"))
                ->orWhere('numero', 'like', "%{$s}%"))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->when($request->client_id, fn($q, $c) => $q->where('client_id', $c))
            ->orderByDesc('date_document')
            ->paginate(20)
            ->withQueryString();

        return view('devis.index', compact('devis'));
    }

    public function create(Request $request)
    {
        $clients       = Client::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();
        $parametres    = ParametresEntreprise::instance();

        $clientSelectionne = $request->client_id ? Client::find($request->client_id) : null;

        $chantierSelectionne = $request->chantier_id
            ? Chantier::with('client')->find($request->chantier_id)
            : null;

        if ($chantierSelectionne && ! $clientSelectionne) {
            $clientSelectionne = $chantierSelectionne->client;
        }

        $chantiers = $clientSelectionne
            ? $clientSelectionne->chantiers()->where('statut', 'actif')->get(['id', 'nom'])
            : collect();

        return view('devis.create', compact(
            'clients', 'modesPaiement', 'tauxTva', 'parametres',
            'clientSelectionne', 'chantiers', 'chantierSelectionne'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'chantier_id'            => 'nullable|exists:chantiers,id',
            'mode_paiement_id'       => 'nullable|exists:modes_paiement,id',
            'statut'                 => 'required|in:brouillon,en_attente,valide',
            'date_document'          => 'required|date',
            'date_validite'          => 'nullable|date',
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

        $devis = DB::transaction(function () use ($data) {
            $devis = Devis::create([
                'numero'            => $this->numerotation->suivant('devis'),
                'client_id'         => $data['client_id'],
                'chantier_id'       => $data['chantier_id'] ?? null,
                'mode_paiement_id'  => $data['mode_paiement_id'] ?? null,
                'created_by'        => auth()->id(),
                'statut'            => $data['statut'],
                'date_document'     => $data['date_document'],
                'date_validite'     => $data['date_validite'] ?? null,
                'frais_port'        => $data['frais_port'] ?? 0,
                'ristourne_globale' => $data['ristourne_globale'] ?? 0,
                'acompte'           => $data['acompte'] ?? 0,
                'delai_reglement'   => $data['delai_reglement'] ?? 30,
                'notes'             => $data['notes'] ?? null,
            ]);

            $this->enregistrerLignes($devis, $data['lignes']);
            $devis->recalculerMontants();
            return $devis;
        });

        return redirect()->route('devis.show', $devis)
            ->with('success', "Devis {$devis->numero} créé.");
    }

    public function show(Devis $devis)
    {
        $devis->load('client', 'chantier', 'modePaiement', 'lignes', 'bonCommande');
        $parametres = ParametresEntreprise::instance();
        $totauxTva  = self::calculerTotauxTva($devis->lignes);

        return view('devis.show', compact('devis', 'parametres', 'totauxTva'));
    }

    public function edit(Devis $devis)
    {
        if ($devis->statut === 'archive') {
            return redirect()->route('devis.show', $devis)->with('error', 'Ce devis est archivé.');
        }

        $devis->load('lignes');
        $clients       = Client::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();
        $chantiers     = $devis->client->chantiers()->where('statut', 'actif')->get(['id', 'nom']);

        return view('devis.edit', compact('devis', 'clients', 'modesPaiement', 'tauxTva', 'chantiers'));
    }

    public function update(Request $request, Devis $devis)
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'chantier_id'            => 'nullable|exists:chantiers,id',
            'mode_paiement_id'       => 'nullable|exists:modes_paiement,id',
            'statut'                 => 'required|in:brouillon,en_attente,valide,refuse,expire,archive',
            'date_document'          => 'required|date',
            'date_validite'          => 'nullable|date',
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

        DB::transaction(function () use ($devis, $data) {
            $devis->update([
                'client_id'         => $data['client_id'],
                'chantier_id'       => $data['chantier_id'] ?? null,
                'mode_paiement_id'  => $data['mode_paiement_id'] ?? null,
                'statut'            => $data['statut'],
                'date_document'     => $data['date_document'],
                'date_validite'     => $data['date_validite'] ?? null,
                'frais_port'        => $data['frais_port'] ?? 0,
                'ristourne_globale' => $data['ristourne_globale'] ?? 0,
                'acompte'           => $data['acompte'] ?? 0,
                'delai_reglement'   => $data['delai_reglement'] ?? 30,
                'notes'             => $data['notes'] ?? null,
            ]);
            $devis->lignes()->delete();
            $this->enregistrerLignes($devis, $data['lignes']);
            $devis->recalculerMontants();
        });

        return redirect()->route('devis.show', $devis)->with('success', 'Devis mis à jour.');
    }

    public function destroy(Devis $devis)
    {
        DB::transaction(function () use ($devis) {
            $devis->lignes()->delete();
            $devis->delete();
        });
        return redirect()->route('devis.index')->with('success', "Devis {$devis->numero} supprimé.");
    }

    public function convertirEnBdc(Devis $devis)
    {
        if ($devis->statut !== 'valide') {
            return back()->with('error', 'Le devis doit être validé pour être converti.');
        }
        if ($devis->bonCommande) {
            return redirect()->route('bons-commande.show', $devis->bonCommande)
                ->with('error', 'Ce devis a déjà un bon de commande.');
        }
        return redirect()->route('bons-commande.create', ['devis_id' => $devis->id]);
    }

    public function envoyer(Request $request, Devis $devis)
    {
        $data = $request->validate([
            'email'   => 'required|email',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            Mail::to($data['email'])->send(new DevisEnvoye($devis, $data['message'] ?? ''));
            return back()->with('success', "Devis {$devis->numero} envoyé à {$data['email']}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur d\'envoi : ' . $e->getMessage() . '. Vérifiez la configuration SMTP dans le fichier .env.');
        }
    }

    public function pdf(Devis $devis)
    {
        $devis->load('client', 'chantier', 'modePaiement', 'lignes');
        $parametres = ParametresEntreprise::instance();
        $totauxTva  = self::calculerTotauxTva($devis->lignes);

        $pdf = Pdf::loadView('pdf.devis', compact('devis', 'parametres', 'totauxTva'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("devis-{$devis->numero}.pdf");
    }

    private function enregistrerLignes($devis, array $lignes): void
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

            $devis->lignes()->create([
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

    public static function calculerTotauxTva($lignes): array
    {
        $totaux = [];
        foreach ($lignes->where('est_section', false) as $ligne) {
            $taux = number_format((float)$ligne->taux_tva, 2);
            $totaux[$taux] = ($totaux[$taux] ?? 0) + ((float)$ligne->montant_ht * ((float)$ligne->taux_tva / 100));
        }
        return $totaux;
    }
}
