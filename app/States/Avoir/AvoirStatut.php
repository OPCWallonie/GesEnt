<?php

namespace App\States\Avoir;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class AvoirStatut extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Brouillon::class)
            ->allowTransition(Brouillon::class, Emis::class)
            ->allowTransition(Emis::class, Archive::class);
    }

    abstract public function label(): string;
    abstract public function couleur(): string;
}
