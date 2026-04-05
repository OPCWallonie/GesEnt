<?php

namespace App\States\BonCommande;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class BdcStatut extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(EnAttente::class)
            ->allowTransition(EnAttente::class, Valide::class)
            ->allowTransition(Valide::class, EnCours::class)
            ->allowTransition(EnCours::class, Termine::class)
            ->allowTransition([EnAttente::class, Valide::class, EnCours::class, Termine::class], Archive::class);
    }

    abstract public function label(): string;
    abstract public function couleur(): string;

    /** Indique si le BDC peut être facturé depuis cet état. */
    public function peutEtreFacture(): bool
    {
        return false;
    }
}
