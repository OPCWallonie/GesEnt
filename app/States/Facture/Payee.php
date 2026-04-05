<?php

namespace App\States\Facture;

class Payee extends FactureStatut
{
    public static $name = 'payee';

    public function label(): string { return 'Payée'; }
    public function couleur(): string { return 'green'; }
}
