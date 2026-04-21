<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevisAnalyseIa extends Model
{
    protected $table = 'devis_analyses_ia';

    protected $fillable = [
        'devis_id',
        'hash_lignes',
        'hash_alternatives',
        'provider',
        'modele',
        'payload_envoye',
        'reponse_brute',
        'analyse',
        'duree_ms',
        'cout_tokens_entree',
        'cout_tokens_sortie',
        'genere_at',
    ];

    protected $casts = [
        'payload_envoye' => 'array',
        'reponse_brute'  => 'array',
        'analyse'        => 'array',
        'genere_at'      => 'datetime',
    ];

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }
}
