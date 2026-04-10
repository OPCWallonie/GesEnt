<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    protected $fillable = [
        'ouvrier_id', 'chantier_id', 'date',
        'heures', 'heures_sup', 'cout_horaire', 'cout_total', 'notes',
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
            // Snapshot du coût horaire si pas fourni
            if (! $pointage->cout_horaire && $pointage->ouvrier_id) {
                $pointage->cout_horaire = Ouvrier::find($pointage->ouvrier_id)?->cout_horaire ?? 0;
            }
            // Heures sup majorées à 50 % (CP124)
            $pointage->cout_total = round(
                ($pointage->heures * $pointage->cout_horaire)
                + ($pointage->heures_sup * $pointage->cout_horaire * 1.5),
                2
            );
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
