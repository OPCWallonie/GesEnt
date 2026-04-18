<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDraft extends Model
{
    protected $fillable = [
        'user_id', 'document_type', 'document_id', 'data', 'saved_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'saved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePourUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $heures = 48)
    {
        return $query->where('saved_at', '>=', now()->subHours($heures));
    }
}
