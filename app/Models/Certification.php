<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = [
        'ouvrier_id', 'type', 'date_obtention', 'date_expiration',
        'organisme', 'numero_certificat', 'document_path', 'notes',
    ];

    protected $casts = [
        'date_obtention'  => 'date',
        'date_expiration' => 'date',
    ];

    // Types de certifications CP124 / construction belge
    // format : 'clé' => ['label' => '...', 'duree_annees' => int|null]
    // duree_annees = null → pas d'expiration automatique
    public const TYPES = [
        'vca'               => ['label' => 'VCA (ouvrier)',                  'duree_annees' => 3],
        'vca_star'          => ['label' => 'VCA* (cadre/superviseur)',       'duree_annees' => 5],
        'echafaudage'       => ['label' => 'Montage/démontage échafaudages', 'duree_annees' => 5],
        'travaux_hauteur'   => ['label' => 'Travaux en hauteur',             'duree_annees' => 3],
        'nacelle'           => ['label' => 'Conduite de nacelle/PEMP',       'duree_annees' => 5],
        'engins_chantier'   => ['label' => 'Engins de chantier / grue',      'duree_annees' => 5],
        'electricite_ba4'   => ['label' => 'Électricité BA4',                'duree_annees' => 3],
        'electricite_ba5'   => ['label' => 'Électricité BA5',                'duree_annees' => 3],
        'amiante'           => ['label' => 'Désamiantage (amiante)',         'duree_annees' => 3],
        'gaz'               => ['label' => 'Travaux sur conduites gaz',      'duree_annees' => 5],
        'sst'               => ['label' => 'Sauveteur Secouriste du Travail','duree_annees' => 2],
        'incendie'          => ['label' => 'Lutte contre l\'incendie',       'duree_annees' => 5],
        'adips'             => ['label' => 'ADIPS (travaux voirie)',         'duree_annees' => 5],
        'aipr'              => ['label' => 'AIPR (proximité réseaux)',       'duree_annees' => 5],
        'adr'               => ['label' => 'ADR (transport matières dang.)', 'duree_annees' => 5],
        'autre'             => ['label' => 'Autre certification',             'duree_annees' => null],
    ];

    // ─── Booted : calcul auto date_expiration ─────────────────────
    protected static function booted(): void
    {
        $calculerExpiration = function (self $cert) {
            if ($cert->date_obtention && empty($cert->date_expiration)) {
                $duree = self::TYPES[$cert->type]['duree_annees'] ?? null;
                if ($duree) {
                    $cert->date_expiration = $cert->date_obtention->copy()->addYears($duree);
                }
            }
        };
        static::creating($calculerExpiration);
        static::updating($calculerExpiration);
    }

    // ─── Relations ────────────────────────────────────────────────
    public function ouvrier()
    {
        return $this->belongsTo(Ouvrier::class);
    }

    // ─── Accessors ────────────────────────────────────────────────
    public function getLibelleTypeAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getEstExpireeAttribute(): bool
    {
        return $this->date_expiration && $this->date_expiration->isPast();
    }

    public function getExpireBientotAttribute(): bool
    {
        if (! $this->date_expiration) {
            return false;
        }
        return ! $this->est_expiree && $this->date_expiration->lte(now()->addDays(90));
    }
}
