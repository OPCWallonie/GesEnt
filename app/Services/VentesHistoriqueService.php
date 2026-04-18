<?php

namespace App\Services;

use App\Models\BonCommande;
use App\Models\CatalogPrixHistorique;
use App\Models\CatalogProduit;
use App\Models\Facture;
use App\Models\LigneDocument;
use Illuminate\Support\Collection;

class VentesHistoriqueService
{
    /**
     * Récupère l'historique des ventes pour un produit, enrichi avec le contexte
     * prix catalogue (pour reconstituer la marge pratiquée à l'époque).
     */
    public function historique(
        ?int $produitId,
        ?int $catalogProduitId,
        ?string $designation,
        ?int $clientId
    ): array {
        $lignes = $this->requeteBase($produitId, $catalogProduitId, $designation)
            ->where('est_section', false)
            ->where('lignes_document.created_at', '>=', now()->subMonths(24))
            ->orderByDesc('lignes_document.created_at')
            ->limit(100)
            ->get();

        $catalogProduit = $catalogProduitId
            ? CatalogProduit::find($catalogProduitId)
            : null;

        $lignesEnrichies = $this->enrichirAvecDocument($lignes, $catalogProduit);

        $ventesCeClient = $clientId
            ? $lignesEnrichies->where('client_id', $clientId)->take(5)
            : collect();

        $ventesAutres = $clientId
            ? $lignesEnrichies->where('client_id', '!=', $clientId)->take(3)
            : $lignesEnrichies->take(5);

        return [
            'ventes_ce_client'      => $ventesCeClient->values()->all(),
            'ventes_autres_clients' => $ventesAutres->values()->all(),
            'stats_ce_client'       => $this->stats($ventesCeClient),
            'stats_toutes'          => $this->stats($lignesEnrichies),
            'prix_catalogue_actuel' => $catalogProduit ? (float) $catalogProduit->prix_catalogue : null,
        ];
    }

    private function requeteBase(?int $produitId, ?int $catalogProduitId, ?string $designation)
    {
        $query = LigneDocument::query()
            ->whereIn('documentable_type', [BonCommande::class, Facture::class]);

        if ($produitId) {
            $query->where('produit_id', $produitId);
        } elseif ($catalogProduitId) {
            $query->where('catalog_produit_id', $catalogProduitId);
        } elseif ($designation && strlen($designation) >= 3) {
            $query->where('designation', $designation);
        } else {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function enrichirAvecDocument(Collection $lignes, ?CatalogProduit $catalogProduit): Collection
    {
        $bdcIds     = $lignes->where('documentable_type', BonCommande::class)->pluck('documentable_id');
        $factureIds = $lignes->where('documentable_type', Facture::class)->pluck('documentable_id');

        $bdcs = $bdcIds->isNotEmpty()
            ? BonCommande::whereIn('id', $bdcIds)
                ->where('statut', '!=', 'archive')
                ->with('client:id,nom', 'chantier:id,nom')
                ->get()->keyBy('id')
            : collect();

        $factures = $factureIds->isNotEmpty()
            ? Facture::whereIn('id', $factureIds)
                ->where('statut', '!=', 'archive')
                ->with('client:id,nom', 'chantier:id,nom')
                ->get()->keyBy('id')
            : collect();

        // Précharger tout l'historique de prix en une seule requête (évite N+1)
        $historiquePrix = $catalogProduit
            ? CatalogPrixHistorique::where('catalog_produit_id', $catalogProduit->id)
                ->orderBy('detected_at')
                ->get()
            : collect();

        return $lignes->map(function ($ligne) use ($bdcs, $factures, $catalogProduit, $historiquePrix) {
            $doc = $ligne->documentable_type === BonCommande::class
                ? $bdcs->get($ligne->documentable_id)
                : $factures->get($ligne->documentable_id);

            if (!$doc) return null;

            $prixVente = (float) $ligne->prix_unitaire;
            $dateVente = $doc->date_document;

            $contexteMarge = null;

            if ($catalogProduit && $prixVente > 0) {
                $prixCatalogueEpoque = $this->prixCatalogueALaDate($historiquePrix, $catalogProduit, $dateVente);
                $prixCatalogueActuel = (float) $catalogProduit->prix_catalogue;

                if ($prixCatalogueEpoque > 0) {
                    $margePctEpoque     = round(($prixVente - $prixCatalogueEpoque) / $prixCatalogueEpoque * 100, 2);
                    $evolutionCatalogue = round(($prixCatalogueActuel - $prixCatalogueEpoque) / $prixCatalogueEpoque * 100, 2);
                    $prixEquivalent     = round($prixCatalogueActuel * (1 + $margePctEpoque / 100), 2);

                    $contexteMarge = [
                        'prix_catalogue_epoque'   => $prixCatalogueEpoque,
                        'prix_catalogue_actuel'   => $prixCatalogueActuel,
                        'marge_pct_epoque'        => $margePctEpoque,
                        'evolution_catalogue_pct' => $evolutionCatalogue,
                        'prix_equivalent_actuel'  => $prixEquivalent,
                        'ecart_prix_pct'          => round(($prixEquivalent - $prixVente) / $prixVente * 100, 2),
                    ];
                }
            }

            return [
                'ligne_id'        => $ligne->id,
                'document_type'   => $ligne->documentable_type === BonCommande::class ? 'bdc' : 'facture',
                'document_numero' => $doc->numero,
                'document_id'     => $doc->id,
                'client_id'       => $doc->client_id,
                'client_nom'      => $doc->client?->nom,
                'chantier_nom'    => $doc->chantier?->nom,
                'date_document'   => $dateVente->toDateString(),
                'age_mois'        => abs((int) $dateVente->diffInMonths(now())),
                'prix_unitaire'   => $prixVente,
                'quantite'        => (float) $ligne->quantite,
                'unite'           => $ligne->unite,
                'designation'     => $ligne->designation,
                'contexte_marge'  => $contexteMarge,
            ];
        })->filter()->values();
    }

    /**
     * Reconstruit le prix catalogue en vigueur à une date donnée.
     *
     * Logique : le premier changement POSTÉRIEUR à $date a un `prix_avant` qui
     * représente le prix qui était en vigueur juste avant ce changement (= à $date).
     * Si aucun changement après $date → le prix actuel était déjà en vigueur.
     *
     * L'historique doit être trié par detected_at ASC.
     */
    private function prixCatalogueALaDate(
        Collection $historiquePrix,
        CatalogProduit $cp,
        \Carbon\Carbon $date
    ): float {
        foreach ($historiquePrix as $changement) {
            if ($changement->detected_at->isAfter($date)) {
                return (float) $changement->prix_avant;
            }
        }

        return (float) $cp->prix_catalogue;
    }

    private function stats(Collection $ventes): array
    {
        if ($ventes->isEmpty()) {
            return ['nb' => 0, 'prix_min' => null, 'prix_moy' => null, 'prix_max' => null, 'marge_moy_pct' => null, 'derniere_date' => null];
        }

        $prix   = $ventes->pluck('prix_unitaire');
        $marges = $ventes->pluck('contexte_marge.marge_pct_epoque')->filter(fn($m) => $m !== null);

        return [
            'nb'            => $ventes->count(),
            'prix_min'      => (float) $prix->min(),
            'prix_moy'      => round((float) $prix->avg(), 2),
            'prix_max'      => (float) $prix->max(),
            'marge_moy_pct' => $marges->isNotEmpty() ? round((float) $marges->avg(), 2) : null,
            'derniere_date' => $ventes->first()['date_document'] ?? null,
        ];
    }
}
