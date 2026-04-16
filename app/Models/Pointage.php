<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    protected $fillable = [
        'ouvrier_id', 'chantier_id', 'date',
        'heures', 'heures_sup', 'mode_heures_sup', 'cout_horaire', 'cout_total', 'notes',
    ];

    protected $casts = [
        'date'         => 'date',
        'heures'       => 'decimal:2',
        'heures_sup'   => 'decimal:2',
        'cout_horaire' => 'decimal:2',
        'cout_total'   => 'decimal:2',
    ];

    protected static function booted(): void
    {
        $calculer = function (self $pointage) {
            $ouvrier = null;

            // Snapshot du coût horaire effectif si pas encore fourni
            if (! $pointage->cout_horaire && $pointage->ouvrier_id) {
                $ouvrier = Ouvrier::find($pointage->ouvrier_id);
                $pointage->cout_horaire = $ouvrier?->cout_horaire_effectif ?? 0;
            }

            $coutBase = (float) $pointage->heures * (float) $pointage->cout_horaire;
            $coutSup  = 0;

            if ((float) $pointage->heures_sup > 0) {
                if ($pointage->mode_heures_sup === 'recuperees') {
                    // Récupérées : coût au taux normal (pas de majoration salariale)
                    // Le travail a bien été fait sur chantier ; la contrepartie est le jour futur.
                    $coutSup = (float) $pointage->heures_sup * (float) $pointage->cout_horaire;
                } else {
                    // Payées : majorées selon la CP (défaut 50%)
                    $ouvrier = $ouvrier ?? Ouvrier::find($pointage->ouvrier_id);
                    $taux    = 1 + ($ouvrier?->taux_majoration ?? 0.50);
                    $coutSup = (float) $pointage->heures_sup * (float) $pointage->cout_horaire * $taux;
                }
            }

            $pointage->cout_total = round($coutBase + $coutSup, 2);
        };

        static::creating($calculer);
        static::updating($calculer);
    }

    // ─── Relations ───────────────────────────────────────────────
    public function ouvrier()
    {
        return $this->belongsTo(Ouvrier::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }
}
