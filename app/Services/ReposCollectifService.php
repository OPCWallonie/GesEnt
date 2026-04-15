<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\ReposCollectif;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReposCollectifService
{
    /**
     * Génère les absences individuelles pour tous les ouvriers concernés.
     * Ignore silencieusement ceux qui ont déjà une absence ce jour-là.
     * Retourne un tableau résumé : ['crees' => int, 'ignores' => int, 'conflits' => Collection].
     */
    public function appliquer(ReposCollectif $rc): array
    {
        $personnel  = $rc->personnelConcerne();
        $conflits   = $rc->detecterConflits()->keyBy('id');
        $crees      = 0;

        DB::transaction(function () use ($rc, $personnel, $conflits, &$crees) {
            foreach ($personnel as $ouvrier) {
                if ($conflits->has($ouvrier->id)) {
                    continue; // déjà absent ce jour-là
                }

                Absence::create([
                    'ouvrier_id'        => $ouvrier->id,
                    'repos_collectif_id' => $rc->id,
                    'date_debut'        => $rc->date,
                    'date_fin'          => $rc->date,
                    'type'              => 'repos_compensatoire',
                    'demi_journee'      => $rc->demi_journee,
                    'justifie'          => true,
                    'motif'             => $rc->libelle,
                ]);

                $crees++;
            }

            $rc->update([
                'applique'   => true,
                'applique_le' => now(),
            ]);
        });

        return [
            'crees'    => $crees,
            'ignores'  => $conflits->count(),
            'conflits' => $conflits->values(),
        ];
    }

    /**
     * Annule un RC collectif : supprime toutes les absences générées automatiquement.
     * Les absences modifiées manuellement après la génération sont préservées si leur
     * date_fin diffère de la date du RC (indication de modification).
     * Retourne le nombre d'absences supprimées.
     */
    public function annuler(ReposCollectif $rc): int
    {
        $count = 0;

        DB::transaction(function () use ($rc, &$count) {
            $count = $rc->absences()->delete();

            $rc->update([
                'applique'    => false,
                'applique_le' => null,
            ]);
        });

        return $count;
    }

    /**
     * Importe un calendrier depuis un contenu CSV.
     * Format attendu (sans en-tête) : date, libellé, demi_journée (0/1), périmètre (tous|cp|type)
     * Retourne ['crees' => int, 'erreurs' => array<string>].
     */
    public function importerCalendrier(string $csvContent): array
    {
        $crees   = 0;
        $erreurs = [];
        $lignes  = array_filter(explode("\n", trim($csvContent)));

        foreach ($lignes as $i => $ligne) {
            $num = $i + 1;
            $cols = str_getcsv($ligne, ';');

            // Accepter aussi virgule comme séparateur si pas de point-virgule
            if (count($cols) < 2) {
                $cols = str_getcsv($ligne, ',');
            }

            if (count($cols) < 2) {
                $erreurs[] = "Ligne {$num} : format invalide (minimum 2 colonnes)";
                continue;
            }

            $dateStr    = trim($cols[0]);
            $libelle    = trim($cols[1]);
            $demiJour   = isset($cols[2]) ? ((int) trim($cols[2]) === 1) : false;
            $perimetre  = isset($cols[3]) ? trim($cols[3]) : 'tous';

            // Validation date
            try {
                $date = \Carbon\Carbon::createFromFormat('d/m/Y', $dateStr);
            } catch (\Exception $e) {
                try {
                    $date = \Carbon\Carbon::parse($dateStr);
                } catch (\Exception $e2) {
                    $erreurs[] = "Ligne {$num} : date invalide « {$dateStr} »";
                    continue;
                }
            }

            if (empty($libelle)) {
                $erreurs[] = "Ligne {$num} : libellé vide";
                continue;
            }

            if (! in_array($perimetre, array_keys(ReposCollectif::PERIMETRES))) {
                $perimetre = 'tous';
            }

            // Éviter les doublons sur même date + libellé
            $existe = ReposCollectif::where('date', $date->toDateString())
                                    ->where('libelle', $libelle)
                                    ->exists();
            if ($existe) {
                $erreurs[] = "Ligne {$num} : doublon ignoré ({$dateStr} — {$libelle})";
                continue;
            }

            ReposCollectif::create([
                'libelle'    => $libelle,
                'date'       => $date,
                'demi_journee' => $demiJour,
                'perimetre'  => $perimetre,
            ]);

            $crees++;
        }

        return ['crees' => $crees, 'erreurs' => $erreurs];
    }
}
