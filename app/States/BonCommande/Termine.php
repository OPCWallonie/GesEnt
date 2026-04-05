<?php

namespace App\States\BonCommande;

class Termine extends BdcStatut
{
    public static $name = 'termine';

    public function label(): string { return 'Terminé'; }
    public function couleur(): string { return 'green'; }
}
