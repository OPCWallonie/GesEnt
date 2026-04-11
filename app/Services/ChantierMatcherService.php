<?php

namespace App\Services;

use App\Models\Chantier;
use App\Models\FactureAchat;

class ChantierMatcherService
{
    /**
     * Tente de trouver le chantier correspondant à une facture d'achat.
     * Retourne un tableau ['chantier_id', 'confiance', 'methode', 'message'] ou null.
     *
     * Stratégie en cascade :
     *   1. Match exact sur la référence extraite par l'IA
     *   2. Match flou (sans tirets/espaces, insensible à la casse)
     *   3. Historique : chantier le plus fréquent chez ce fournisseur dans les 3 derniers mois
     */
    public function trouverChantier(?string $referenceExtraite, ?int $fournisseurId): ?array
    {
        // ── 1. Match exact ────────────────────────────────────────────────────
        if ($referenceExtraite) {
            $chantier = Chantier::where('reference', $referenceExtraite)
                ->where('statut', '!=', 'archive')
                ->first();

            if ($chantier) {
                return [
                    'chantier_id' => $chantier->id,
                    'confiance'   => 'haute',
                    'methode'     => 'reference_exacte',
                    'message'     => "Chantier identifié par référence : {$chantier->reference} — {$chantier->nom}",
                ];
            }

            // ── 2. Match flou (enlève séparateurs, insensible casse) ──────────
            $refNormalisee = strtoupper(preg_replace('/[\s\-_.]/', '', $referenceExtraite));

            $chantier = Chantier::where('statut', '!=', 'archive')
                ->whereNotNull('reference')
                ->get(['id', 'nom', 'reference', 'statut'])
                ->first(function (Chantier $c) use ($refNormalisee) {
                    $refChantier = strtoupper(preg_replace('/[\s\-_.]/', '', $c->reference ?? ''));
                    return $refChantier === $refNormalisee;
                });

            if ($chantier) {
                return [
                    'chantier_id' => $chantier->id,
                    'confiance'   => 'moyenne',
                    'methode'     => 'reference_floue',
                    'message'     => "Chantier probable (référence similaire) : {$chantier->reference} — {$chantier->nom}",
                ];
            }
        }

        // ── 3. Historique fournisseur (3 derniers mois) ──────────────────────
        if ($fournisseurId) {
            $ligne = FactureAchat::where('fournisseur_id', $fournisseurId)
                ->whereNotNull('chantier_id')
                ->where('date_document', '>=', now()->subMonths(3))
                ->selectRaw('chantier_id, COUNT(*) as nb')
                ->groupBy('chantier_id')
                ->orderByDesc('nb')
                ->first();

            if ($ligne) {
                $chantier = Chantier::find($ligne->chantier_id);
                if ($chantier && $chantier->statut !== 'archive') {
                    return [
                        'chantier_id' => $chantier->id,
                        'confiance'   => 'basse',
                        'methode'     => 'historique_fournisseur',
                        'message'     => "Suggestion basée sur l'historique : ce fournisseur livre habituellement le chantier « {$chantier->nom} »",
                    ];
                }
            }
        }

        return null;
    }
}
