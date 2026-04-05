<?php

namespace App\States\Facture;

class EnAttente extends FactureStatut
{
    public static $name = 'en_attente';

    public function label(): string { return 'En attente'; }
    public function couleur(): string { return 'gray'; }
}
