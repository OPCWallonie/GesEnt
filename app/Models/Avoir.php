<?php

namespace App\Models;

use App\States\Avoir\AvoirStatut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;

class Avoir extends Model
{
    use SoftDeletes, HasStates;

    protected $fillable = [
        'numero', 'facture_id', 'client_id', 'chantier_id', 'created_by',
        'statut', 'date_document', 'motif',
        'montant_ht', 'taux_tva', 'montant_tva', 'montant_ttc',
        'notes',
        'peppol_reference', 'peppol_envoye_at',
        'odoo_move_id', 'odoo_synced_at',
    ];

    protected $casts = [
        'statut'         => AvoirStatut::class,
        'date_document'  => 'date',
        'montant_ht'     => 'decimal:4',
        'taux_tva'       => 'decimal:2',
        'montant_tva'    => 'decimal:4',
        'montant_ttc'    => 'decimal:4',
        'peppol_envoye_at' => 'datetime',
        'odoo_synced_at'   => 'datetime',
    ];

    public function estBrouillon(): bool
    {
        return $this->statut instanceof \App\States\Avoir\Brouillon;
    }

    public function estEmis(): bool
    {
        return $this->statut instanceof \App\States\Avoir\Emis;
    }

    public function estArchive(): bool
    {
        return $this->statut instanceof \App\States\Avoir\Archive;
    }

    public function peutEtreModifie(): bool
    {
        return false;
    }

    public function peutEtreSupprime(): bool
    {
        return $this->estBrouillon();
    }

    public function peutEtreArchive(): bool
    {
        return $this->estEmis();
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
