<?php

namespace App\States\Devis;

class Expire extends DevisStatut
{
    public static $name = 'expire';

    public function label(): string { return 'Expiré'; }
    public function couleur(): string { return 'orange'; }
}
