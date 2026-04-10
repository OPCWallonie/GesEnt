<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'ouvrier_id', 'date_debut', 'date_fin',
        'type', 'justifie', 'motif',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'justifie'   => 'boolean',
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

    // ─── Accessors ───────────────────────────────────────────────
    public function getNbJoursAttribute(): int
    {
        return (int) $this->date_debut->diffInWeekdays($this->date_fin) + 1;
    }

    public function getLibelleTypeAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
