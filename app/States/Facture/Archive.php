<?php

namespace App\States\Facture;

class Archive extends FactureStatut
{
    public static $name = 'archive';

    public function label(): string { return 'Archivée'; }
    public function couleur(): string { return 'gray'; }
}
