<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteurNumerotation extends Model
{
    protected $table = 'compteurs_numerotation';

    protected $fillable = ['type', 'annee', 'compteur'];
}
