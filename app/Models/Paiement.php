<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'facture_id', 'created_by', 'date_paiement',
        'montant', 'mode', 'reference', 'notes',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'montant'       => 'decimal:4',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
