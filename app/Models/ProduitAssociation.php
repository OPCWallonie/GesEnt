<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduitAssociation extends Model
{
    protected $table = 'produit_associations';

    protected $fillable = ['produit_a', 'produit_b', 'nb_cooccurrences', 'score'];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    /**
     * Trouver les clés des produits associés à un produit donné, triés par score.
     */
    public static function associesDe(string $produitKey, int $limit = 10): array
    {
        return static::where('produit_a', $produitKey)
            ->orWhere('produit_b', $produitKey)
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->map(fn($a) => $a->produit_a === $produitKey ? $a->produit_b : $a->produit_a)
            ->toArray();
    }
}
