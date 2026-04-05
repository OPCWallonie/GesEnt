<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    protected $table = 'modes_paiement';

    protected $fillable = ['nom', 'instructions', 'defaut', 'actif'];

    protected $casts = [
        'defaut' => 'boolean',
        'actif'  => 'boolean',
    ];

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
