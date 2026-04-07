<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kit extends Model
{
    protected $fillable = [
        'nom', 'description', 'categorie', 'created_by', 'nb_utilisations',
    ];

    public function lignes()
    {
        return $this->hasMany(KitLigne::class)->orderBy('ordre');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function estimationHt(): float
    {
        return $this->lignes
            ->where('est_section', false)
            ->sum(fn($l) => $l->prix_unitaire * $l->quantite);
    }
}
