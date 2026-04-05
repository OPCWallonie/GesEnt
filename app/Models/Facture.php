<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'bon_commande_id', 'client_id', 'chantier_id',
        'mode_paiement_id', 'created_by',
        'statut', 'date_document', 'date_echeance',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'frais_port', 'ristourne_globale', 'acompte_deduit',
        'retenue_garantie_pct', 'retenue_garantie_montant', 'retenue_garantie_liberee_at',
        'montant_net_a_payer', 'delai_reglement',
        'date_paiement', 'montant_paye', 'montant_total_paye', 'notes',
        'nb_relances', 'derniere_relance_at', 'prochaine_relance_at', 'relance_auto',
        'numero_situation', 'pourcentage_avancement', 'pourcentage_cumule', 'montant_anterieur',
    ];

    protected $casts = [
        'date_document'                => 'date',
        'date_echeance'                => 'date',
        'date_paiement'                => 'date',
        'derniere_relance_at'          => 'date',
        'retenue_garantie_liberee_at'  => 'date',
        'montant_ht'                   => 'decimal:4',
        'montant_tva'                  => 'decimal:4',
        'montant_ttc'                  => 'decimal:4',
        'frais_port'                   => 'decimal:4',
        'ristourne_globale'            => 'decimal:2',
        'acompte_deduit'               => 'decimal:4',
        'retenue_garantie_pct'         => 'decimal:2',
        'retenue_garantie_montant'     => 'decimal:4',
        'montant_net_a_payer'          => 'decimal:4',
        'montant_paye'                 => 'decimal:4',
        'montant_total_paye'           => 'decimal:4',
        'nb_relances'                  => 'integer',
        'prochaine_relance_at'         => 'date',
        'relance_auto'                 => 'boolean',
        'numero_situation'             => 'integer',
        'pourcentage_avancement'       => 'decimal:2',
        'pourcentage_cumule'           => 'decimal:2',
        'montant_anterieur'            => 'decimal:4',
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class);
    }

    public function lignes()
    {
        return $this->morphMany(LigneDocument::class, 'documentable')->orderBy('ordre');
    }

    public function estEnRetard(): bool
    {
        return $this->date_echeance
            && $this->date_echeance->isPast()
            && in_array($this->statut, ['en_attente', 'envoyee']);
    }

    public function resteAPayer(): float
    {
        return max(0, $this->montant_net_a_payer - $this->montant_paye);
    }

    public function getMontantRestantAttribute(): float
    {
        return max(0, $this->montant_net_a_payer - $this->montant_total_paye);
    }

    public function getEstTotalementPayeeAttribute(): bool
    {
        return $this->montant_total_paye >= $this->montant_net_a_payer;
    }

    public function recalculerPaiements(): void
    {
        $total           = $this->paiements()->sum('montant');
        $dernierPaiement = $this->paiements()->latest('date_paiement')->first();

        $this->update([
            'montant_total_paye' => $total,
            'montant_paye'       => $total,
            'date_paiement'      => $dernierPaiement?->date_paiement,
            'statut'             => $total >= $this->montant_net_a_payer ? 'payee' : $this->statut,
        ]);
    }

    public function enregistrerRelance(): void
    {
        $this->increment('nb_relances');
        $this->update([
            'derniere_relance_at'  => now()->toDateString(),
            'prochaine_relance_at' => now()->addDays(14)->toDateString(),
        ]);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class)->orderBy('date_paiement');
    }

    public function avoirs()
    {
        return $this->hasMany(\App\Models\Avoir::class);
    }

    public function totalAvoirs(): float
    {
        return (float) $this->avoirs()->sum('montant_ttc');
    }
}
