<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelanceEtape extends Model
{
    protected $fillable = [
        'relance_scenario_id',
        'numero_ordre',
        'delai_jours',
        'sujet',
        'corps_email',
        'canal',
        'ton',
        'actif',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function scenario()
    {
        return $this->belongsTo(RelanceScenario::class, 'relance_scenario_id');
    }

    /**
     * Whether a courrier PDF should be attached (canal = courrier or les_deux).
     */
    public function avecCourrier(): bool
    {
        return in_array($this->canal, ['courrier', 'les_deux']);
    }

    /**
     * Whether an email should be sent (canal = mail or les_deux).
     */
    public function avecMail(): bool
    {
        return in_array($this->canal, ['mail', 'les_deux']);
    }
}
