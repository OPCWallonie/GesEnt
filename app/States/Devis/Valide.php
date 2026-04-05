<?php

namespace App\States\Devis;

class Valide extends DevisStatut
{
    public static $name = 'valide';

    public function label(): string { return 'Validé'; }
    public function couleur(): string { return 'green'; }
}
