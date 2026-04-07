<?php

namespace App\Services;

use App\Models\CatalogProduit;
use App\Models\LigneDocument;
use App\Models\ProduitAssociation;
use App\Models\ProduitUsageStat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProduitUsageService
{
    /**
     * Enregistrer l'utilisation des produits d'un document (devis, BDC, facture).
     * Appelé après l'enregistrement des lignes.
     */
    public function enregistrerUtilisation(Model $document): void
    {
        $lignes = $document->lignes()->where('est_section', false)->get();

        foreach ($lignes as $ligne) {
            $produitId        = $ligne->produit_id;
            $catalogProduitId = $ligne->catalog_produit_id;

            // Si aucun identifiant, essayer de matcher par désignation dans le catalogue
            if (!$produitId && !$catalogProduitId) {
                $match = CatalogProduit::where('designation', $ligne->designation)->first();
                if ($match) {
                    $catalogProduitId = $match->id;
                } else {
                    continue; // Ligne libre non identifiable
                }
            }

            $critere = $produitId
                ? ['produit_id' => $produitId]
                : ['catalog_produit_id' => $catalogProduitId];

            $stat = ProduitUsageStat::firstOrCreate($critere, [
                'nb_utilisations' => 0,
                'nb_devis'        => 0,
            ]);

            // Incrémente nb_devis seulement si pas utilisé aujourd'hui
            $usedToday = $stat->derniere_utilisation && $stat->derniere_utilisation->isToday();

            $stat->increment('nb_utilisations');
            if (!$usedToday) {
                $stat->increment('nb_devis');
            }
            $stat->derniere_utilisation = now()->toDateString();
            $stat->recalculerScore();
        }

        $this->enregistrerAssociations($document);
    }

    /**
     * Enregistrer les associations (co-occurrences) d'un document.
     */
    public function enregistrerAssociations(Model $document): void
    {
        $lignes = $document->lignes()->where('est_section', false)->get();

        $produitKeys = [];
        foreach ($lignes as $ligne) {
            if ($ligne->produit_id) {
                $produitKeys[] = 'p:' . $ligne->produit_id;
            } elseif ($ligne->catalog_produit_id) {
                $produitKeys[] = 'c:' . $ligne->catalog_produit_id;
            } else {
                $match = CatalogProduit::where('designation', $ligne->designation)->first();
                if ($match) {
                    $produitKeys[] = 'c:' . $match->id;
                }
            }
        }

        $produitKeys = array_unique($produitKeys);
        if (count($produitKeys) < 2) {
            return;
        }

        for ($i = 0; $i < count($produitKeys); $i++) {
            for ($j = $i + 1; $j < count($produitKeys); $j++) {
                $a = min($produitKeys[$i], $produitKeys[$j]);
                $b = max($produitKeys[$i], $produitKeys[$j]);

                $assoc = ProduitAssociation::firstOrCreate(
                    ['produit_a' => $a, 'produit_b' => $b],
                    ['nb_cooccurrences' => 0, 'score' => 0]
                );
                $assoc->increment('nb_cooccurrences');
                $assoc->update(['score' => $assoc->nb_cooccurrences]);
            }
        }
    }

    /**
     * Recalculer tous les scores (pour la tâche planifiée).
     */
    public function recalculerTousLesScores(): int
    {
        $count = 0;
        ProduitUsageStat::chunk(200, function ($stats) use (&$count) {
            foreach ($stats as $stat) {
                $stat->recalculerScore();
                $count++;
            }
        });
        return $count;
    }

    /**
     * Initialiser les stats depuis l'historique existant (à exécuter une seule fois).
     */
    public function initialiserDepuisHistorique(): array
    {
        $inseres = 0;

        $usages = LigneDocument::whereNotNull('produit_id')
            ->where('est_section', false)
            ->select('produit_id', DB::raw('COUNT(*) as nb'), DB::raw('MAX(created_at) as dernier'))
            ->groupBy('produit_id')
            ->get();

        foreach ($usages as $usage) {
            $stat = ProduitUsageStat::updateOrCreate(
                ['produit_id' => $usage->produit_id],
                [
                    'nb_utilisations'      => $usage->nb,
                    'nb_devis'             => $usage->nb,
                    'derniere_utilisation' => $usage->dernier,
                ]
            );
            $stat->recalculerScore();
            $inseres++;
        }

        // Également les lignes avec catalog_produit_id
        $usagesCatalog = LigneDocument::whereNotNull('catalog_produit_id')
            ->where('est_section', false)
            ->select('catalog_produit_id', DB::raw('COUNT(*) as nb'), DB::raw('MAX(created_at) as dernier'))
            ->groupBy('catalog_produit_id')
            ->get();

        foreach ($usagesCatalog as $usage) {
            $stat = ProduitUsageStat::updateOrCreate(
                ['catalog_produit_id' => $usage->catalog_produit_id],
                [
                    'nb_utilisations'      => $usage->nb,
                    'nb_devis'             => $usage->nb,
                    'derniere_utilisation' => $usage->dernier,
                ]
            );
            $stat->recalculerScore();
            $inseres++;
        }

        return ['produits' => $inseres];
    }
}
