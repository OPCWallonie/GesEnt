<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailEnvoi extends Model
{
    protected $fillable = [
        'document_type', 'document_id',
        'sent_by', 'destinataire', 'sujet', 'message',
        'statut', 'erreur', 'envoye_at',
    ];

    protected $casts = [
        'envoye_at' => 'datetime',
    ];

    public function document(): MorphTo
    {
        return $this->morphTo();
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
