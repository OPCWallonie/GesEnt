<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Avenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'bon_commande_id', 'numero_ordre', 'created_by',
        'statut', 'date_document', 'objet',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'frais_port', 'acompte', 'notes',
    ];

    protected $casts = [
        'date_document' => 'date',
        'montant_ht'    => 'decimal:4',
        'montant_tva'   => 'decimal:4',
        'montant_ttc'   => 'decimal:4',
        'frais_port'    => 'decimal:4',
        'acompte'       => 'decimal:4',
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class);
    }

    public function lignes()
    {
        return $this->morphMany(LigneDocument::class, 'documentable')->orderBy('ordre');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function peutEtreModifie(): bool
    {
        return in_array($this->statut, ['brouillon', 'en_attente'], true);
    }

    public function peutEtreSupprime(): bool
    {
        return in_array($this->statut, ['brouillon', 'en_attente'], true);
    }

    public function peutEtreArchive(): bool
    {
        if ($this->statut === 'archive') return false;
        if ($this->bonCommande && $this->bonCommande->factures()->exists()) {
            return false;
        }
        return true;
    }
}
