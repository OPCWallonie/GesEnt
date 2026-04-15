<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'ouvrier_id', 'repos_collectif_id', 'date_debut', 'date_fin',
        'type', 'demi_journee', 'justifie', 'motif',
    ];

    protected $casts = [
        'date_debut'   => 'date',
        'date_fin'     => 'date',
        'justifie'     => 'boolean',
        'demi_journee' => 'boolean',
    ];

    public const TYPES = [
        'conge'               => 'Congé payé',
        'maladie'             => 'Maladie',
        'accident_travail'    => 'Accident du travail',
        'repos_compensatoire' => 'Repos compensatoire',
        'autre'               => 'Autre',
    ];

    // ─── Relations ───────────────────────────────────────────────
    public function ouvrier()
    {
        return $this->belongsTo(Ouvrier::class);
    }

    public function reposCollectif()
    {
        return $this->belongsTo(ReposCollectif::class);
    }

    // ─── Accessors ───────────────────────────────────────────────
    public function getNbJoursAttribute(): float
    {
        if ($this->demi_journee) {
            return 0.5;
        }
        return (float) ($this->date_debut->diffInWeekdays($this->date_fin) + 1);
    }

    public function getLibelleTypeAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
