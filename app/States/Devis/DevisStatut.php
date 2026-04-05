<?php

namespace App\States\Devis;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class DevisStatut extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Brouillon::class)
            ->allowTransition(Brouillon::class, EnAttente::class)
            ->allowTransition(EnAttente::class, Valide::class)
            ->allowTransition(EnAttente::class, Refuse::class)
            ->allowTransition(EnAttente::class, Expire::class)
            ->allowTransition(Valide::class, Archive::class)
            ->allowTransition([Brouillon::class, EnAttente::class, Refuse::class, Expire::class], Archive::class);
    }

    abstract public function label(): string;
    abstract public function couleur(): string;
}
