<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DocumentService
{
    /**
     * Enregistrer les lignes d'un document (devis, BDC, avenant ou facture).
     * Le document doit utiliser la relation morphMany 'lignes'.
     */
    public function enregistrerLignes(Model $document, array $lignes): void
    {
        foreach ($lignes as $ordre => $ligneData) {
            $estSection = !empty($ligneData['est_section']);
            $montantHt  = 0;

            if (!$estSection) {
                $brut   = (float)($ligneData['prix_unitaire'] ?? 0) * (float)($ligneData['quantite'] ?? 1);
                $remise = ($ligneData['remise_type'] ?? 'montant') === 'pourcentage'
                    ? $brut * ((float)($ligneData['remise_valeur'] ?? 0) / 100)
                    : (float)($ligneData['remise_valeur'] ?? 0);
                $montantHt = max(0, $brut - $remise);
            }

            $document->lignes()->create([
                'ordre'             => $ordre,
                'est_section'       => $estSection,
                'produit_id'        => $ligneData['produit_id'] ?? null,
                'catalog_produit_id'=> $ligneData['catalog_produit_id'] ?? null,
                'designation'       => $ligneData['designation'],
                'detail'            => $ligneData['detail'] ?? null,
                'unite'             => $ligneData['unite'] ?? 'pièce',
                'quantite'          => $ligneData['quantite'] ?? 1,
                'prix_unitaire'     => $ligneData['prix_unitaire'] ?? 0,
                'remise_valeur'     => $ligneData['remise_valeur'] ?? 0,
                'remise_type'       => $ligneData['remise_type'] ?? 'montant',
                'taux_tva'          => $ligneData['taux_tva'] ?? 21,
                'montant_ht'        => $montantHt,
            ]);
        }
    }

    /**
     * Recalculer les montants HT/TVA/TTC d'un document.
     * Gère les champs spécifiques aux factures (acompte_deduit, retenue_garantie_pct).
     */
    public function recalculerMontants(Model $document): void
    {
        $lignes = $document->lignes;
        $ht  = $lignes->where('est_section', false)->sum('montant_ht');
        $tva = $lignes->where('est_section', false)->sum(
            fn($l) => $l->montant_ht * ($l->taux_tva / 100)
        );

        $ristourne = $ht * (($document->ristourne_globale ?? 0) / 100);
        $htNet     = $ht - $ristourne + ($document->frais_port ?? 0);
        $tvaNet    = $tva * (1 - ($document->ristourne_globale ?? 0) / 100);
        $ttc       = $htNet + $tvaNet;

        $updates = [
            'montant_ht'  => $htNet,
            'montant_tva' => $tvaNet,
            'montant_ttc' => $ttc,
        ];

        // Champs spécifiques aux factures
        if ($document->getTable() === 'factures') {
            $base      = max(0, $ttc - ($document->acompte_deduit ?? 0));
            $retenue   = $base * (($document->retenue_garantie_pct ?? 0) / 100);
            $netAPayer = max(0, $base - $retenue);

            $updates['retenue_garantie_montant'] = $retenue;
            $updates['montant_net_a_payer']       = $netAPayer;
        }

        $document->update($updates);
    }

    /**
     * Calculer les totaux TVA ventilés par taux (pour l'affichage).
     * Retourne [taux => montant_tva] ex: ['21.00' => 1234.56, '6.00' => 78.90]
     */
    public function calculerTotauxTva(Collection $lignes): array
    {
        $totaux = [];
        foreach ($lignes->where('est_section', false) as $ligne) {
            $taux          = number_format((float)$ligne->taux_tva, 2);
            $totaux[$taux] = ($totaux[$taux] ?? 0) + ((float)$ligne->montant_ht * ((float)$ligne->taux_tva / 100));
        }
        return $totaux;
    }
}
