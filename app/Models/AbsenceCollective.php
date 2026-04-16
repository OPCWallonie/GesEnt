<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AbsenceCollective extends Model
{
    protected $table = 'absences_collectives';

    protected $fillable = [
        'type_collectif', 'libelle', 'date', 'demi_journee',
        'perimetre', 'perimetre_valeurs',
        'notes', 'applique', 'applique_le',
    ];

    protected $casts = [
        'date'              => 'date',
        'demi_journee'      => 'boolean',
        'perimetre_valeurs' => 'array',
        'applique'          => 'boolean',
        'applique_le'       => 'datetime',
    ];

    public const TYPES_COLLECTIFS = [
        'repos_compensatoire' => 'Repos compensatoire',
        'report_ferie'        => 'Report de jour férié',
        'conge_entreprise'    => 'Congé d\'entreprise',
    ];

    public const PERIMETRES = [
        'tous' => 'Tout le personnel planifiable',
        'cp'   => 'Par commission paritaire',
        'type' => 'Par type de personnel',
    ];

    // ─── Relations ───────────────────────────────────────────────

    public function absences()
    {
        return $this->hasMany(Absence::class, 'absence_collective_id');
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getNbJoursAttribute(): float
    {
        return $this->demi_journee ? 0.5 : 1.0;
    }

    public function getLibellePerimetreAttribute(): string
    {
        return self::PERIMETRES[$this->perimetre] ?? $this->perimetre;
    }

    public function getLabelTypeAttribute(): string
    {
        return self::TYPES_COLLECTIFS[$this->type_collectif] ?? $this->type_collectif;
    }

    /**
     * Quel type d'absence individuelle créer quand on applique ce collectif.
     */
    public function getTypeAbsenceIndividuelleAttribute(): string
    {
        return match ($this->type_collectif) {
            'repos_compensatoire' => 'repos_compensatoire',
            'report_ferie'        => 'report_ferie',
            'conge_entreprise'    => 'conge_entreprise',
            default               => 'autre',
        };
    }

    // ─── Méthodes ────────────────────────────────────────────────

    /**
     * Retourne le Builder des ouvriers concernés.
     */
    public function personnelConcerneQuery(): Builder
    {
        $query = Ouvrier::planifiable()->where('actif', true);

        if ($this->perimetre === 'cp' && ! empty($this->perimetre_valeurs)) {
            $query->whereIn('commission_paritaire', $this->perimetre_valeurs);
        } elseif ($this->perimetre === 'type' && ! empty($this->perimetre_valeurs)) {
            $query->whereIn('type_personnel', $this->perimetre_valeurs);
        }

        return $query;
    }

    public function personnelConcerne()
    {
        return $this->personnelConcerneQuery()->get();
    }

    /**
     * Retourne les ouvriers concernés qui ont déjà une absence ce jour-là.
     */
    public function detecterConflits(): \Illuminate\Support\Collection
    {
        $ids = $this->personnelConcerneQuery()->pluck('id');

        return Ouvrier::whereIn('id', $ids)
            ->whereHas('absences', function ($q) {
                $q->whereDate('date_debut', '<=', $this->date)
                  ->whereDate('date_fin', '>=', $this->date);
            })
            ->get();
    }
}
