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
        'date_paiement', 'montant_paye', 'notes',
        'nb_relances', 'derniere_relance_at',
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
        'nb_relances'                  => 'integer',
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

    public function enregistrerRelance(): void
    {
        $this->increment('nb_relances');
        $this->update(['derniere_relance_at' => now()->toDateString()]);
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
