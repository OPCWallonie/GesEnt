<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Avoir extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'facture_id', 'client_id', 'chantier_id', 'created_by',
        'date_document', 'motif',
        'montant_ht', 'taux_tva', 'montant_tva', 'montant_ttc',
        'notes',
        'peppol_reference', 'peppol_envoye_at',
    ];

    protected $casts = [
        'date_document'  => 'date',
        'montant_ht'     => 'decimal:4',
        'taux_tva'       => 'decimal:2',
        'montant_tva'    => 'decimal:4',
        'montant_ttc'    => 'decimal:4',
        'peppol_envoye_at' => 'datetime',
    ];

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
