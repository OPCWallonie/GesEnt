<?php

namespace App\Services;

use App\Models\ChargeFonctionnement;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\ParametresEntreprise;
use App\Models\Pointage;
use Illuminate\Support\Collection;

class FraisGenerauxService
{
    /**
     * Total mensuel des frais généraux pour un mois donné.
     * Inclut : personnel indirect (employe_admin + direction) + charges de fonctionnement.
     */
    public function totalMensuel(int $annee, int $mois): float
    {
        // Personnel indirect : cout_mensuel ou cout_horaire * 164.54
        $coutPersonnel = Ouvrier::fraisGeneraux()
            ->where('actif', true)
            ->get()
            ->sum(function (Ouvrier $p) {
                if ($p->cout_mensuel > 0) {
                    return (float) $p->cout_mensuel;
                }
                return round((float) $p->cout_horaire * 164.54, 2);
            });

        // Charges de fonctionnement actives ce mois
        $coutCharges = ChargeFonctionnement::activesAuMois($annee, $mois)
            ->get()
            ->sum('montant_mensuel_normalise');

        return round($coutPersonnel + $coutCharges, 2);
    }

    /**
     * Répartit les frais généraux sur les chantiers pour une période.
     * Retourne une Collection [chantier_id => quote_part_euros].
     */
    public function repartir(int $annee, ?int $mois = null): Collection
    {
        $cle = ParametresEntreprise::instance()->cle_repartition_frais ?? 'prorata_heures';

        $totalFrais = $mois
            ? $this->totalMensuel($annee, $mois)
            : collect(range(1, 12))->sum(fn($m) => $this->totalMensuel($annee, $m));

        if ($totalFrais <= 0) {
            return collect();
        }

        $chantiersActifs = Chantier::whereNotIn('statut', ['archive'])->pluck('id');

        if ($chantiersActifs->isEmpty()) {
            return collect();
        }

        return match ($cle) {
            'prorata_heures' => $this->repartirParHeures($chantiersActifs, $totalFrais, $annee, $mois),
            'prorata_ca'     => $this->repartirParCA($chantiersActifs, $totalFrais),
            'uniforme'       => $this->repartirUniforme($chantiersActifs, $totalFrais),
            default          => $this->repartirParHeures($chantiersActifs, $totalFrais, $annee, $mois),
        };
    }

    private function repartirParHeures(Collection $chantierIds, float $totalFrais, int $annee, ?int $mois): Collection
    {
        $query = Pointage::whereIn('chantier_id', $chantierIds)
            ->whereYear('date', $annee);

        if ($mois) {
            $query->whereMonth('date', $mois);
        }

        $heuresParChantier = $query
            ->selectRaw('chantier_id, SUM(heures + heures_sup) as total_heures')
            ->groupBy('chantier_id')
            ->pluck('total_heures', 'chantier_id');

        $totalHeures = $heuresParChantier->sum();

        // Fallback uniforme si aucun pointage sur la période
        if ($totalHeures <= 0) {
            return $this->repartirUniforme($chantierIds, $totalFrais);
        }

        return $heuresParChantier->map(fn($h) => round(($h / $totalHeures) * $totalFrais, 2));
    }

    private function repartirParCA(Collection $chantierIds, float $totalFrais): Collection
    {
        $caParChantier = Chantier::whereIn('id', $chantierIds)
            ->with('factures')
            ->get()
            ->mapWithKeys(function (Chantier $c) {
                $ca = $c->totalVentes();
                return [$c->id => $ca];
            })
            ->filter(fn($ca) => $ca > 0);

        $totalCA = $caParChantier->sum();

        if ($totalCA <= 0) {
            return $this->repartirUniforme($chantierIds, $totalFrais);
        }

        return $caParChantier->map(fn($ca) => round(($ca / $totalCA) * $totalFrais, 2));
    }

    private function repartirUniforme(Collection $chantierIds, float $totalFrais): Collection
    {
        $count = $chantierIds->count();
        $part  = round($totalFrais / $count, 2);

        return $chantierIds->mapWithKeys(fn($id) => [$id => $part]);
    }
}
