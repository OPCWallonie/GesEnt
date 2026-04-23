<?php

namespace App\States\Avoir;

class Brouillon extends AvoirStatut
{
    public static $name = 'brouillon';

    public function label(): string { return 'Brouillon'; }
    public function couleur(): string { return 'gray'; }
}
