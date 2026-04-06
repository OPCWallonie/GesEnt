<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeppolWebhookLog extends Model
{
    protected $fillable = [
        'provider', 'event_type', 'document_id', 'status',
        'error_message', 'payload', 'facture_achat_id',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function factureAchat()
    {
        return $this->belongsTo(FactureAchat::class);
    }
}
