<?php

namespace App\States\Avoir;

class Archive extends AvoirStatut
{
    public static $name = 'archive';

    public function label(): string { return 'Archivé'; }
    public function couleur(): string { return 'gray'; }
}
