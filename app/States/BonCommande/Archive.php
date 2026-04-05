<?php

namespace App\States\BonCommande;

class Archive extends BdcStatut
{
    public static $name = 'archive';

    public function label(): string { return 'Archivé'; }
    public function couleur(): string { return 'gray'; }
}
