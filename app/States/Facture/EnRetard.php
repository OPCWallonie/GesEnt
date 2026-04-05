<?php

namespace App\States\Facture;

class EnRetard extends FactureStatut
{
    public static $name = 'en_retard';

    public function label(): string { return 'En retard'; }
    public function couleur(): string { return 'red'; }
}
