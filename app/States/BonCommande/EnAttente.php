<?php

namespace App\States\BonCommande;

class EnAttente extends BdcStatut
{
    public static $name = 'en_attente';

    public function label(): string { return 'En attente'; }
    public function couleur(): string { return 'gray'; }
}
