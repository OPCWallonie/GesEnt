<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'designation', 'description', 'unite',
        'prix_unitaire', 'taux_tva', 'categorie',
        'fournisseur', 'reference_fournisseur', 'actif',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:4',
        'taux_tva'      => 'decimal:2',
        'actif'         => 'boolean',
    ];

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
