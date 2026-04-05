<?php

namespace App\Services;

use App\Models\CompteurNumerotation;
use Illuminate\Support\Facades\DB;

class NumerotationService
{
    private const PREFIXES = [
        'devis'         => 'DEV',
        'bon_commande'  => 'BDC',
        'facture'       => 'FAC',
        'facture_achat' => 'FACH',
        'avoir'         => 'AVO',
    ];

    public function suivant(string $type): string
    {
        if (! array_key_exists($type, self::PREFIXES)) {
            throw new \InvalidArgumentException("Type de document inconnu : {$type}");
        }

        $annee = (int) now()->format('Y');

        return DB::transaction(function () use ($type, $annee) {
            $compteur = CompteurNumerotation::lockForUpdate()
                ->firstOrCreate(
                    ['type' => $type, 'annee' => $annee],
                    ['compteur' => 0]
                );

            $compteur->increment('compteur');
            $compteur->refresh();

            return sprintf(
                '%s/%d/%04d',
                self::PREFIXES[$type],
                $annee,
                $compteur->compteur
            );
        });
    }

    public function suivantAvenant(string $numeroBdc, int $nombreExistants): string
    {
        return $numeroBdc . '/' . ($nombreExistants + 1);
    }

    public static function formater(string $numero): string
    {
        return $numero;
    }
}
