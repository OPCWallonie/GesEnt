<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitLigne extends Model
{
    protected $fillable = [
        'kit_id', 'ordre', 'est_section',
        'produit_id', 'catalog_produit_id',
        'designation', 'detail', 'unite',
        'quantite', 'prix_unitaire',
        'remise_valeur', 'remise_type', 'taux_tva',
    ];

    protected $casts = [
        'est_section'   => 'boolean',
        'quantite'      => 'decimal:4',
        'prix_unitaire' => 'decimal:4',
        'remise_valeur' => 'decimal:4',
        'taux_tva'      => 'decimal:2',
    ];

    public function kit()
    {
        return $this->belongsTo(Kit::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function catalogProduit()
    {
        return $this->belongsTo(CatalogProduit::class);
    }
}
