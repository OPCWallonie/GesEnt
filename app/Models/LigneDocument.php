<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneDocument extends Model
{
    protected $table = 'lignes_document';

    protected $fillable = [
        'documentable_type', 'documentable_id',
        'produit_id', 'ordre', 'est_section',
        'designation', 'detail', 'unite',
        'quantite', 'prix_unitaire',
        'remise_valeur', 'remise_type',
        'taux_tva', 'montant_ht',
    ];

    protected $casts = [
        'est_section'   => 'boolean',
        'quantite'      => 'decimal:4',
        'prix_unitaire' => 'decimal:4',
        'remise_valeur' => 'decimal:4',
        'taux_tva'      => 'decimal:2',
        'montant_ht'    => 'decimal:4',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // Calcule et met à jour montant_ht selon quantité, prix, remise
    public function calculerMontant(): float
    {
        $brut = $this->prix_unitaire * $this->quantite;

        $remise = match ($this->remise_type) {
            'pourcentage' => $brut * ($this->remise_valeur / 100),
            default       => $this->remise_valeur,
        };

        return max(0, $brut - $remise);
    }

    public function getMontantTvaAttribute(): float
    {
        return $this->montant_ht * ($this->taux_tva / 100);
    }

    public function getMontantTtcAttribute(): float
    {
        return $this->montant_ht + $this->montant_tva;
    }
}
