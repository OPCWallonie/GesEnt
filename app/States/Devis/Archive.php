<?php

namespace App\States\Devis;

class Archive extends DevisStatut
{
    public static $name = 'archive';

    public function label(): string { return 'Archivé'; }
    public function couleur(): string { return 'gray'; }
}
