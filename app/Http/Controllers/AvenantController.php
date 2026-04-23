<?php

namespace App\Http\Controllers;

use App\Models\Avenant;
use App\Models\BonCommande;
use App\Models\ModePaiement;
use App\Models\TauxTva;
use App\Services\DocumentService;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvenantController extends Controller
{
    public function __construct(
        private NumerotationService $numerotation,
        private DocumentService $documentService,
    ) {}

    public function create(BonCommande $bonCommande)
    {
        $modesPaiement = ModePaiement::actif()->orderBy('nom')->get();
        $tauxTva       = TauxTva::orderBy('taux')->get();
        $numeroOrdre   = $bonCommande->avenants()->count() + 1;

        return view('avenants.create', compact('bonCommande', 'modesPaiement', 'tauxTva', 'numeroOrdre'));
    }

    public function store(Request $request, BonCommande $bonCommande)
    {
        $data = $request->validate([
            'objet'                  => 'nullable|string|max:255',
            'date_document'          => 'required|date',
            'frais_port'             => 'nullable|numeric|min:0',
            'acompte'                => 'nullable|numeric|min:0',
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

        $avenant = DB::transaction(function () use ($bonCommande, $data) {
            $numeroOrdre = $bonCommande->avenants()->count() + 1;
            $numero      = $this->numerotation->suivantAvenant($bonCommande->numero, $numeroOrdre - 1);

            $avenant = $bonCommande->avenants()->create([
                'numero'        => $numero,
                'numero_ordre'  => $numeroOrdre,
                'created_by'    => auth()->id(),
                'statut'        => 'en_attente',
                'date_document' => $data['date_document'],
                'objet'         => $data['objet'] ?? null,
                'frais_port'    => $data['frais_port'] ?? 0,
                'acompte'       => $data['acompte'] ?? 0,
                'notes'         => $data['notes'] ?? null,
            ]);

            $this->documentService->enregistrerLignes($avenant, $data['lignes']);
            $this->documentService->recalculerMontants($avenant);
            return $avenant;
        });

        return redirect()->route('bons-commande.show', $bonCommande)
            ->with('success', "Avenant {$avenant->numero} ajouté.");
    }

    public function show(Avenant $avenant)
    {
        $avenant->load('bonCommande.client', 'lignes');
        return view('avenants.show', compact('avenant'));
    }

    public function edit(Avenant $avenant)
    {
        $avenant->load('lignes', 'bonCommande');
        $tauxTva = TauxTva::orderBy('taux')->get();
        return view('avenants.edit', compact('avenant', 'tauxTva'));
    }

    public function update(Request $request, Avenant $avenant)
    {
        $data = $request->validate([
            'objet'                  => 'nullable|string|max:255',
            'statut'                 => 'required|in:en_attente,valide,archive',
            'date_document'          => 'required|date',
            'frais_port'             => 'nullable|numeric|min:0',
            'acompte'                => 'nullable|numeric|min:0',
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

        DB::transaction(function () use ($avenant, $data) {
            $avenant->update([
                'objet'         => $data['objet'] ?? null,
                'statut'        => $data['statut'],
                'date_document' => $data['date_document'],
                'frais_port'    => $data['frais_port'] ?? 0,
                'acompte'       => $data['acompte'] ?? 0,
                'notes'         => $data['notes'] ?? null,
            ]);
            $avenant->lignes()->delete();
            $this->documentService->enregistrerLignes($avenant, $data['lignes']);
            $this->documentService->recalculerMontants($avenant);
        });

        return redirect()->route('bons-commande.show', $avenant->bonCommande)
            ->with('success', "Avenant {$avenant->numero} mis à jour.");
    }

    public function archiver(Avenant $avenant)
    {
        if (! $avenant->peutEtreArchive()) {
            return back()->with('error', 'Cet avenant est déjà archivé.');
        }
        $avenant->update(['statut' => 'archive']);
        return redirect()->route('avenants.show', $avenant)->with('success', "Avenant {$avenant->numero} archivé.");
    }

    public function destroy(Avenant $avenant)
    {
        if (! $avenant->peutEtreSupprime()) {
            return back()->with('error', 'Cet avenant est archivé et ne peut pas être supprimé.');
        }
        $bdc = $avenant->bonCommande;
        DB::transaction(function () use ($avenant) {
            $avenant->lignes()->delete();
            $avenant->delete();
        });
        return redirect()->route('bons-commande.show', $bdc)
            ->with('success', "Avenant {$avenant->numero} supprimé.");
    }

}
