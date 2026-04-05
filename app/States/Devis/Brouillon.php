<?php

namespace App\States\Devis;

class Brouillon extends DevisStatut
{
    public static $name = 'brouillon';

    public function label(): string { return 'Brouillon'; }
    public function couleur(): string { return 'gray'; }
}
