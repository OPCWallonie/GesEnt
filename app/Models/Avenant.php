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

    public function scopeSansArchives($query)
    {
        return $query->where('statut', '!=', 'archive');
    }

    public function scopeUniquementArchives($query)
    {
        return $query->where('statut', 'archive');
    }

    public function peutEtreModifie(): bool
    {
        return $this->statut !== 'archive';
    }

    public function peutEtreSupprime(): bool
    {
        return $this->statut !== 'archive';
    }

    public function peutEtreArchive(): bool
    {
        return $this->statut !== 'archive';
    }
}
