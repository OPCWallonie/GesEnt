<?php

namespace App\States\Devis;

class Refuse extends DevisStatut
{
    public static $name = 'refuse';

    public function label(): string { return 'Refusé'; }
    public function couleur(): string { return 'red'; }
}
