<?php

namespace App\States\Facture;

class Envoyee extends FactureStatut
{
    public static $name = 'envoyee';

    public function label(): string { return 'Envoyée'; }
    public function couleur(): string { return 'blue'; }
}
