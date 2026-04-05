<?php

namespace App\States\Devis;

class EnAttente extends DevisStatut
{
    public static $name = 'en_attente';

    public function label(): string { return 'En attente'; }
    public function couleur(): string { return 'yellow'; }
}
