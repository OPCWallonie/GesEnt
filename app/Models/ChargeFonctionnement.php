<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargeFonctionnement extends Model
{
    protected $table = 'charges_fonctionnement';

    protected $fillable = [
        'libelle', 'categorie', 'montant_mensuel',
        'date_debut', 'date_fin', 'periodicite', 'notes', 'actif',
    ];

    protected $casts = [
        'montant_mensuel' => 'decimal:2',
        'date_debut'      => 'date',
        'date_fin'        => 'date',
        'actif'           => 'boolean',
    ];

    public const CATEGORIES = [
        'locaux'     => 'Locaux (loyer, RC, taxe poubelle…)',
        'energie'    => 'Énergie (électricité, gaz, eau)',
        'vehicules'  => 'Véhicules (leasing, carburant, entretien)',
        'assurances' => 'Assurances (RC, accidents travail, incendie)',
        'telecom'    => 'Télécom & IT (internet, GSM, logiciels)',
        'comptable'  => 'Comptable / Secrétariat social',
        'entretien'  => 'Entretien (nettoyage, vêtements, matériel)',
        'divers'     => 'Divers (fournitures, représentation…)',
    ];

    public const PERIODICITES = [
        'mensuel'     => 'Mensuel',
        'trimestriel' => 'Trimestriel',
        'annuel'      => 'Annuel',
    ];

    /**
     * Coût mensuel normalisé quelle que soit la périodicité.
     * Le champ `montant_mensuel` stocke le montant DANS la périodicité choisie.
     */
    public function getMontantMensuelNormaliseAttribute(): float
    {
        return match ($this->periodicite) {
            'trimestriel' => round((float) $this->montant_mensuel / 3, 2),
            'annuel'      => round((float) $this->montant_mensuel / 12, 2),
            default       => (float) $this->montant_mensuel,
        };
    }

    /**
     * Scope : charges actives sur un mois donné.
     */
    public function scopeActivesAuMois($query, int $annee, int $mois)
    {
        $debut = \Carbon\Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = $debut->copy()->endOfMonth();

        return $query->where('actif', true)
            ->where('date_debut', '<=', $fin)
            ->where(fn($q) => $q->whereNull('date_fin')->orWhere('date_fin', '>=', $debut));
    }
}
