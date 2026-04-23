<?php

namespace App\States\Avoir;

class Emis extends AvoirStatut
{
    public static $name = 'emis';

    public function label(): string { return 'Émis'; }
    public function couleur(): string { return 'blue'; }
}
