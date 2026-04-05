<?php

namespace App\States\Facture;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class FactureStatut extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(EnAttente::class)
            ->allowTransition(EnAttente::class, Envoyee::class)
            ->allowTransition(EnAttente::class, Payee::class)
            ->allowTransition(Envoyee::class, EnRetard::class)
            ->allowTransition(Envoyee::class, Payee::class)
            ->allowTransition(EnRetard::class, Payee::class)
            ->allowTransition(EnRetard::class, Envoyee::class)
            ->allowTransition([EnAttente::class, Envoyee::class, EnRetard::class], Archive::class)
            ->allowTransition(Payee::class, Archive::class);
    }

    abstract public function label(): string;
    abstract public function couleur(): string;
}
