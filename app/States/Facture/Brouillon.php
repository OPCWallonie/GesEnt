<?php

namespace App\States\Facture;

class Brouillon extends FactureStatut
{
    public static $name = 'brouillon';

    public function label(): string { return 'Brouillon'; }
    public function couleur(): string { return 'gray'; }
}
