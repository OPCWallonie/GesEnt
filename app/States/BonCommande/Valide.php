<?php

namespace App\States\BonCommande;

class Valide extends BdcStatut
{
    public static $name = 'valide';

    public function label(): string { return 'Validé'; }
    public function couleur(): string { return 'blue'; }
    public function peutEtreFacture(): bool { return true; }
}
