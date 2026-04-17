<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPrixHistorique extends Model
{
    protected $table = 'catalog_prix_historique';

    protected $fillable = [
        'catalog_produit_id', 'fournisseur', 'reference',
        'prix_avant', 'prix_apres', 'variation_pct',
        'est_significatif', 'source', 'detected_at',
    ];

    protected $casts = [
        'prix_avant'       => 'decimal:4',
        'prix_apres'       => 'decimal:4',
        'variation_pct'    => 'decimal:2',
        'est_significatif' => 'boolean',
        'detected_at'      => 'datetime',
    ];

    public function catalogProduit()
    {
        return $this->belongsTo(CatalogProduit::class);
    }

    public function scopeSignificatifs($query)
    {
        return $query->where('est_significatif', true);
    }

    public function scopeDepuis($query, \Carbon\Carbon $date)
    {
        return $query->where('detected_at', '>=', $date);
    }

    public function scopeHausses($query)
    {
        return $query->where('variation_pct', '>', 0);
    }

    public function scopeBaisses($query)
    {
        return $query->where('variation_pct', '<', 0);
    }

    public function getTypeVariationAttribute(): string
    {
        return $this->variation_pct > 0 ? 'hausse' : 'baisse';
    }
}
