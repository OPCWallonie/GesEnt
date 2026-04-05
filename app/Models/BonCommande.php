<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonCommande extends Model
{
    use SoftDeletes;

    protected $table = 'bons_commande';

    protected $fillable = [
        'numero', 'devis_id', 'client_id', 'chantier_id', 'mode_paiement_id', 'created_by',
        'statut', 'date_document', 'date_debut_travaux', 'date_fin_prevue',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'frais_port', 'ristourne_globale', 'acompte', 'delai_reglement',
        'notes', 'date_statut',
    ];

    protected $casts = [
        'date_document'     => 'date',
        'date_debut_travaux' => 'date',
        'date_fin_prevue'   => 'date',
        'date_statut'       => 'date',
        'montant_ht'        => 'decimal:4',
        'montant_tva'       => 'decimal:4',
        'montant_ttc'       => 'decimal:4',
        'frais_port'        => 'decimal:4',
        'ristourne_globale' => 'decimal:2',
        'acompte'           => 'decimal:4',
    ];

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class);
    }

    public function avenants()
    {
        return $this->hasMany(Avenant::class)->orderBy('numero_ordre');
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    public function lignes()
    {
        return $this->morphMany(LigneDocument::class, 'documentable')->orderBy('ordre');
    }

    // Toutes les lignes : BDC + avenants regroupés pour facturation
    public function toutesLesLignes()
    {
        $lignesBdc = $this->lignes;
        $lignesAvenants = $this->avenants->flatMap->lignes;
        return $lignesBdc->concat($lignesAvenants);
    }

    public function montantTotalAvecAvenants(): array
    {
        $avenants = $this->avenants;
        return [
            'ht'  => $this->montant_ht + $avenants->sum('montant_ht'),
            'tva' => $this->montant_tva + $avenants->sum('montant_tva'),
            'ttc' => $this->montant_ttc + $avenants->sum('montant_ttc'),
            'frais_port' => $this->frais_port + $avenants->sum('frais_port'),
            'acompte'    => $this->acompte + $avenants->sum('acompte'),
        ];
    }

    public function peutEtreFacture(): bool
    {
        if ($this->statut !== 'valide') {
            return false;
        }
        return $this->avenants->every(fn($a) => $a->statut === 'valide');
    }
}
