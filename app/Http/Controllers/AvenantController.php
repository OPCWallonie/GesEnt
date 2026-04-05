<?php

namespace App\Http\Controllers;

use App\Models\Avenant;
use App\Models\BonCommande;
use App\Models\ModePaiement;
use App\Models\TauxTva;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvenantController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

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

            $this->enregistrerLignes($avenant, $data['lignes']);
            $this->recalculerMontants($avenant);
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
            $this->enregistrerLignes($avenant, $data['lignes']);
            $this->recalculerMontants($avenant);
        });

        return redirect()->route('bons-commande.show', $avenant->bonCommande)
            ->with('success', "Avenant {$avenant->numero} mis à jour.");
    }

    public function destroy(Avenant $avenant)
    {
        $bdc = $avenant->bonCommande;
        DB::transaction(function () use ($avenant) {
            $avenant->lignes()->delete();
            $avenant->delete();
        });
        return redirect()->route('bons-commande.show', $bdc)
            ->with('success', "Avenant {$avenant->numero} supprimé.");
    }

    private function enregistrerLignes($avenant, array $lignes): void
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

            $avenant->lignes()->create([
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

    private function recalculerMontants(Avenant $avenant): void
    {
        $lignes = $avenant->lignes;
        $ht  = $lignes->where('est_section', false)->sum('montant_ht');
        $tva = $lignes->where('est_section', false)->sum(
            fn($l) => $l->montant_ht * ($l->taux_tva / 100)
        );
        $avenant->update([
            'montant_ht'  => $ht + $avenant->frais_port,
            'montant_tva' => $tva,
            'montant_ttc' => $ht + $avenant->frais_port + $tva,
        ]);
    }
}
