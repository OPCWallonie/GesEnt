<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelanceScenario extends Model
{
    protected $fillable = ['nom', 'description', 'est_defaut'];

    protected $casts = ['est_defaut' => 'boolean'];

    public function etapes()
    {
        return $this->hasMany(RelanceEtape::class)->orderBy('numero_ordre');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Retourne le scénario par défaut, ou null si aucun n'existe.
     */
    public static function parDefaut(): ?self
    {
        return static::where('est_defaut', true)->first();
    }

    /**
     * Définit ce scénario comme défaut (et désactive les autres).
     */
    public function definirDefaut(): void
    {
        static::where('est_defaut', true)->update(['est_defaut' => false]);
        $this->update(['est_defaut' => true]);
    }
}
