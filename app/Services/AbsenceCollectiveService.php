<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\AbsenceCollective;
use Illuminate\Support\Facades\DB;

class AbsenceCollectiveService
{
    /**
     * Génère les absences individuelles pour tous les ouvriers concernés.
     * Le type d'absence créée dépend du type_collectif.
     * Retourne un tableau résumé : ['crees' => int, 'ignores' => int, 'conflits' => Collection].
     */
    public function appliquer(AbsenceCollective $ac): array
    {
        $personnel  = $ac->personnelConcerne();
        $conflits   = $ac->detecterConflits()->keyBy('id');
        $crees      = 0;

        DB::transaction(function () use ($ac, $personnel, $conflits, &$crees) {
            foreach ($personnel as $ouvrier) {
                if ($conflits->has($ouvrier->id)) {
                    continue;
                }

                Absence::create([
                    'ouvrier_id'             => $ouvrier->id,
                    'absence_collective_id'  => $ac->id,
                    'date_debut'             => $ac->date,
                    'date_fin'               => $ac->date,
                    'type'                   => $ac->type_absence_individuelle,
                    'demi_journee'           => $ac->demi_journee,
                    'justifie'               => true,
                    'motif'                  => $ac->label_type . ' : ' . $ac->libelle,
                ]);

                $crees++;
            }

            $ac->update([
                'applique'    => true,
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
     * Annule une absence collective : supprime toutes les absences individuelles liées.
     */
    public function annuler(AbsenceCollective $ac): int
    {
        $count = 0;

        DB::transaction(function () use ($ac, &$count) {
            $count = $ac->absences()->delete();

            $ac->update([
                'applique'    => false,
                'applique_le' => null,
            ]);
        });

        return $count;
    }
}
