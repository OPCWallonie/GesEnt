<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom', 'contact', 'email', 'telephone',
        'numero_tva', 'numero_entreprise',
        'adresse', 'code_postal', 'ville', 'pays',
        'notes', 'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function facturesAchat()
    {
        return $this->hasMany(FactureAchat::class);
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function totalAchats(): float
    {
        return (float) $this->facturesAchat()->sum('montant_ttc');
    }
}
