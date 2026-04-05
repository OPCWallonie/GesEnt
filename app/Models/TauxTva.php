<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TauxTva extends Model
{
    protected $table = 'taux_tva';

    protected $fillable = ['taux', 'libelle', 'defaut'];

    protected $casts = [
        'taux'   => 'decimal:2',
        'defaut' => 'boolean',
    ];

    public static function defaut(): self
    {
        return static::where('defaut', true)->first()
            ?? static::orderByDesc('taux')->first();
    }
}
