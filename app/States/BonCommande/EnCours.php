<?php

namespace App\States\BonCommande;

class EnCours extends BdcStatut
{
    public static $name = 'en_cours';

    public function label(): string { return 'En cours'; }
    public function couleur(): string { return 'yellow'; }
    public function peutEtreFacture(): bool { return true; }
}
