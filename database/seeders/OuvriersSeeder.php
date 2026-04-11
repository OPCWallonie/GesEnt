<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Pointage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OuvriersSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Ouvriers ──────────────────────────────────────────────────────
        /** @var Ouvrier $marc */
        $marc = Ouvrier::create([
            'nom'            => 'Dubois',
            'prenom'         => 'Marc',
            'metier'         => 'Maçon',
            'categorie'      => 'III',
            'cout_horaire'   => 38.50,
            'date_entree'    => '2019-03-15',
            'qualifications' => ['VCA', 'BA4'],
            'actif'          => true,
        ]);

        /** @var Ouvrier $kevin */
        $kevin = Ouvrier::create([
            'nom'            => 'Lejeune',
            'prenom'         => 'Kevin',
            'metier'         => 'Coffreur',
            'categorie'      => 'II',
            'cout_horaire'   => 34.00,
            'date_entree'    => '2021-09-02',
            'qualifications' => ['VCA'],
            'actif'          => true,
        ]);

        /** @var Ouvrier $youssef */
        $youssef = Ouvrier::create([
            'nom'            => 'Amrani',
            'prenom'         => 'Youssef',
            'metier'         => 'Carreleur',
            'categorie'      => 'IIIA',
            'cout_horaire'   => 40.00,
            'date_entree'    => '2018-01-10',
            'qualifications' => ['VCA', 'BA5', 'Nacelle'],
            'actif'          => true,
        ]);

        /** @var Ouvrier $thomas */
        $thomas = Ouvrier::create([
            'nom'            => 'Pirard',
            'prenom'         => 'Thomas',
            'metier'         => 'Manœuvre',
            'categorie'      => 'I',
            'cout_horaire'   => 28.50,
            'date_entree'    => '2024-06-06',
            'qualifications' => ['VCA'],
            'actif'          => true,
        ]);

        /** @var Ouvrier $luca */
        $luca = Ouvrier::create([
            'nom'            => 'Moretti',
            'prenom'         => 'Luca',
            'metier'         => 'Plaquiste',
            'categorie'      => 'II',
            'cout_horaire'   => 35.00,
            'date_entree'    => '2020-04-22',
            'qualifications' => ['VCA', 'Nacelle'],
            'actif'          => false,
        ]);

        /** @var Ouvrier $stephane */
        $stephane = Ouvrier::create([
            'nom'            => 'Collin',
            'prenom'         => 'Stéphane',
            'metier'         => 'Contremaître',
            'categorie'      => 'IV',
            'cout_horaire'   => 45.00,
            'date_entree'    => '2015-02-03',
            'qualifications' => ['VCA', 'BA4', 'BA5', 'Secouriste'],
            'actif'          => true,
        ]);

        // ─── 2. Chantiers actifs (les 4 premiers uniques) ─────────────────────
        $chantiers = Chantier::whereIn('statut', ['actif'])
            ->orderBy('id')
            ->get()
            ->unique('nom')
            ->values();

        if ($chantiers->count() < 4) {
            $this->command->warn('Moins de 4 chantiers actifs — pointages partiels.');
        }

        // c1=Rénovation bureaux, c2=Construction appts, c3=Salle de bain, c4=Extension brassage
        $c1 = $chantiers->get(0);
        $c2 = $chantiers->get(1);
        $c3 = $chantiers->get(2);
        $c4 = $chantiers->get(3);

        // ─── 3. Pointages (4 semaines glissantes) ─────────────────────────────
        // Chaque entrée : [ouvrier, chantier, jour (0=lun … 4=ven), heures_normales, heures_sup]
        // Semaines : S-3, S-2, S-1, S-0 (semaine courante)

        $today = Carbon::today(); // 2026-04-11

        // Dates maladie Thomas (semaine dernière mar+mer)
        $semainePrec  = $today->copy()->subWeek()->startOfWeek();
        $thomasMalade = [
            $semainePrec->copy()->addDay()->format('Y-m-d'),   // mardi S-1
            $semainePrec->copy()->addDays(2)->format('Y-m-d'), // mercredi S-1
        ];

        // Planning explicite : [semaine_offset, ouvrier, chantier, jour_semaine(0=lun), h_normales, h_sup]
        $entries = [
            // ── Semaine S-3 (il y a 3 semaines) ──────────────────────────────
            // Marc : lun-mar sur c1, mer-ven sur c2
            [3, $marc,     $c1, 0, 8.0, 0.0],
            [3, $marc,     $c1, 1, 8.0, 0.0],
            [3, $marc,     $c2, 2, 8.0, 0.0],
            [3, $marc,     $c2, 3, 8.0, 1.5], // jeudi sup
            [3, $marc,     $c2, 4, 8.0, 2.0], // vendredi sup

            // Kevin : lun-mer sur c1, jeu-ven sur c3
            [3, $kevin,    $c1, 0, 8.0, 0.0],
            [3, $kevin,    $c1, 1, 8.0, 0.0],
            [3, $kevin,    $c1, 2, 8.0, 0.0],
            [3, $kevin,    $c3, 3, 8.0, 1.5],
            [3, $kevin,    $c3, 4, 8.0, 0.0],

            // Youssef : lun-jeu sur c2, vendredi c3
            [3, $youssef,  $c2, 0, 8.0, 0.0],
            [3, $youssef,  $c2, 1, 8.0, 0.0],
            [3, $youssef,  $c2, 2, 8.0, 0.0],
            [3, $youssef,  $c2, 3, 8.0, 1.5],
            [3, $youssef,  $c3, 4, 8.0, 2.0],

            // Thomas : toute la semaine c4
            [3, $thomas,   $c4, 0, 8.0, 0.0],
            [3, $thomas,   $c4, 1, 8.0, 0.0],
            [3, $thomas,   $c4, 2, 8.0, 0.0],
            [3, $thomas,   $c4, 3, 8.0, 1.5],
            [3, $thomas,   $c4, 4, 7.5, 0.0],

            // Stéphane : lun-mar c2, mer-ven c4 (contremaître, supervision)
            [3, $stephane, $c2, 0, 8.0, 0.0],
            [3, $stephane, $c2, 1, 8.0, 0.0],
            [3, $stephane, $c4, 2, 8.0, 0.0],
            [3, $stephane, $c4, 3, 8.0, 2.0],
            [3, $stephane, $c4, 4, 8.0, 2.0],

            // ── Semaine S-2 ───────────────────────────────────────────────────
            // Marc : toute la semaine c2
            [2, $marc,     $c2, 0, 8.0, 0.0],
            [2, $marc,     $c2, 1, 8.0, 0.0],
            [2, $marc,     $c2, 2, 8.0, 0.0],
            [2, $marc,     $c2, 3, 8.0, 1.5],
            [2, $marc,     $c4, 4, 8.0, 2.0], // vendredi renforts c4

            // Kevin : lun-jeu c2, ven c3
            [2, $kevin,    $c2, 0, 8.0, 0.0],
            [2, $kevin,    $c2, 1, 8.0, 0.0],
            [2, $kevin,    $c3, 2, 8.0, 0.0],
            [2, $kevin,    $c3, 3, 8.0, 1.5],
            [2, $kevin,    $c3, 4, 8.0, 0.0],

            // Youssef : lun-mer c1, jeu-ven c3
            [2, $youssef,  $c1, 0, 8.0, 0.0],
            [2, $youssef,  $c1, 1, 8.0, 0.0],
            [2, $youssef,  $c1, 2, 8.0, 0.0],
            [2, $youssef,  $c3, 3, 8.0, 2.0],
            [2, $youssef,  $c3, 4, 8.0, 2.0],

            // Thomas : lun-mer c4, jeu-ven c1
            [2, $thomas,   $c4, 0, 8.0, 0.0],
            [2, $thomas,   $c4, 1, 8.0, 0.0],
            [2, $thomas,   $c1, 2, 8.0, 0.0],
            [2, $thomas,   $c1, 3, 8.0, 0.0],
            [2, $thomas,   $c1, 4, 7.0, 0.0],

            // Stéphane : lun-jeu c2, ven c4
            [2, $stephane, $c2, 0, 8.0, 0.0],
            [2, $stephane, $c2, 1, 8.0, 0.0],
            [2, $stephane, $c2, 2, 8.0, 0.0],
            [2, $stephane, $c2, 3, 8.0, 1.5],
            [2, $stephane, $c4, 4, 8.0, 2.0],

            // ── Semaine S-1 (Thomas absent mar+mer) ──────────────────────────
            // Marc : lun-mer c1, jeu-ven c3
            [1, $marc,     $c1, 0, 8.0, 0.0],
            [1, $marc,     $c1, 1, 8.0, 0.0],
            [1, $marc,     $c3, 2, 8.0, 0.0],
            [1, $marc,     $c3, 3, 8.0, 1.5],
            [1, $marc,     $c3, 4, 8.0, 2.0],

            // Kevin : toute la semaine c1, jeu c4
            [1, $kevin,    $c1, 0, 8.0, 0.0],
            [1, $kevin,    $c1, 1, 8.0, 0.0],
            [1, $kevin,    $c1, 2, 8.0, 0.0],
            [1, $kevin,    $c4, 3, 8.0, 2.0],
            [1, $kevin,    $c4, 4, 8.0, 0.0],

            // Youssef : lun-mer c2, jeu-ven c4
            [1, $youssef,  $c2, 0, 8.0, 0.0],
            [1, $youssef,  $c2, 1, 8.0, 0.0],
            [1, $youssef,  $c2, 2, 8.0, 0.0],
            [1, $youssef,  $c4, 3, 8.0, 1.5],
            [1, $youssef,  $c4, 4, 8.0, 2.0],

            // Thomas : lun c3, (mar+mer = absent), jeu-ven c4
            [1, $thomas,   $c3, 0, 8.0, 0.0],
            // mar, mer : maladie → pas de pointage
            [1, $thomas,   $c4, 3, 7.5, 1.0], // retour jeudi
            [1, $thomas,   $c4, 4, 8.0, 0.0],

            // Stéphane : lun-mar c2, mer-ven c1
            [1, $stephane, $c2, 0, 8.0, 0.0],
            [1, $stephane, $c2, 1, 8.0, 0.0],
            [1, $stephane, $c1, 2, 8.0, 0.0],
            [1, $stephane, $c1, 3, 8.0, 1.5],
            [1, $stephane, $c1, 4, 8.0, 2.0],

            // ── Semaine S-0 (semaine courante, jusqu'au vendredi 10/04) ───────
            // Marc : lun-ven c2
            [0, $marc,     $c2, 0, 8.0, 0.0],
            [0, $marc,     $c2, 1, 8.0, 0.0],
            [0, $marc,     $c2, 2, 8.0, 0.0],
            [0, $marc,     $c2, 3, 8.0, 1.5],
            [0, $marc,     $c2, 4, 8.0, 2.0],

            // Kevin : lun-ven c3
            [0, $kevin,    $c3, 0, 8.0, 0.0],
            [0, $kevin,    $c3, 1, 8.0, 0.0],
            [0, $kevin,    $c3, 2, 8.0, 0.0],
            [0, $kevin,    $c3, 3, 8.0, 1.5],
            [0, $kevin,    $c3, 4, 8.0, 0.0],

            // Youssef : lun-mer c1, jeu-ven c2
            [0, $youssef,  $c1, 0, 8.0, 0.0],
            [0, $youssef,  $c1, 1, 8.0, 0.0],
            [0, $youssef,  $c1, 2, 8.0, 0.0],
            [0, $youssef,  $c2, 3, 8.0, 2.0],
            [0, $youssef,  $c2, 4, 8.0, 0.0],

            // Thomas : toute la semaine c4
            [0, $thomas,   $c4, 0, 8.0, 0.0],
            [0, $thomas,   $c4, 1, 8.0, 0.0],
            [0, $thomas,   $c4, 2, 8.0, 0.0],
            [0, $thomas,   $c4, 3, 8.0, 1.0],
            [0, $thomas,   $c4, 4, 7.5, 0.0],

            // Stéphane : lun-jeu c2, ven c1 (supervision générale)
            [0, $stephane, $c2, 0, 8.0, 0.0],
            [0, $stephane, $c2, 1, 8.0, 0.0],
            [0, $stephane, $c2, 2, 8.0, 0.0],
            [0, $stephane, $c2, 3, 8.0, 2.0],
            [0, $stephane, $c1, 4, 8.0, 2.0],
        ];

        foreach ($entries as [$semOffset, $ouvrier, $chantier, $jourIndex, $heures, $heuresSup]) {
            if ($chantier === null) continue;

            $lundi = $today->copy()->subWeeks($semOffset)->startOfWeek();
            $date  = $lundi->copy()->addDays($jourIndex)->format('Y-m-d');

            // Ne pas créer de pointage les jours d'absence de Thomas
            if ($ouvrier->id === $thomas->id && in_array($date, $thomasMalade)) {
                continue;
            }

            Pointage::updateOrCreate(
                [
                    'ouvrier_id'  => $ouvrier->id,
                    'chantier_id' => $chantier->id,
                    'date'        => $date,
                ],
                [
                    'heures'       => $heures,
                    'heures_sup'   => $heuresSup,
                    'cout_horaire' => $ouvrier->cout_horaire,
                ]
            );
        }

        // ─── 4. Absences ──────────────────────────────────────────────────────

        // Thomas Pirard — 2 jours maladie mardi + mercredi semaine S-1
        Absence::create([
            'ouvrier_id' => $thomas->id,
            'date_debut' => $semainePrec->copy()->addDay()->format('Y-m-d'),
            'date_fin'   => $semainePrec->copy()->addDays(2)->format('Y-m-d'),
            'type'       => 'maladie',
            'justifie'   => true,
            'motif'      => 'Certificat médical fourni',
        ]);

        // Marc Dubois — 1 jour repos compensatoire il y a ~10 jours (vendredi ouvrable)
        $reposMarcDate = $today->copy()->subDays(10);
        // Ramener au vendredi précédent si week-end
        while ($reposMarcDate->isWeekend()) {
            $reposMarcDate->subDay();
        }
        Absence::create([
            'ouvrier_id' => $marc->id,
            'date_debut' => $reposMarcDate->format('Y-m-d'),
            'date_fin'   => $reposMarcDate->format('Y-m-d'),
            'type'       => 'repos_compensatoire',
            'justifie'   => true,
            'motif'      => 'Récupération heures supplémentaires S8',
        ]);

        // Luca Moretti — accident du travail depuis le 01/03/2026
        Absence::create([
            'ouvrier_id' => $luca->id,
            'date_debut' => '2026-03-01',
            'date_fin'   => '2026-06-30',
            'type'       => 'accident_travail',
            'justifie'   => true,
            'motif'      => "Chute d'échafaudage — suivi médical en cours",
        ]);

        $nb = Pointage::whereIn('ouvrier_id', [$marc->id, $kevin->id, $youssef->id, $thomas->id, $stephane->id])->count();
        $this->command->info("OuvriersSeeder terminé : 6 ouvriers, {$nb} pointages, 3 absences.");
    }
}
