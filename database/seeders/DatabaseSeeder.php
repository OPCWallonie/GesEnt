<?php

namespace Database\Seeders;

use App\Models\ModePaiement;
use App\Models\TauxTva;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Rôles
        $admin    = Role::firstOrCreate(['name' => 'admin']);
        $comptable = Role::firstOrCreate(['name' => 'comptable']);
        Role::firstOrCreate(['name' => 'lecture']);

        // Utilisateur admin par défaut — mot de passe aléatoire, affiché une seule fois
        $password = Str::random(16);
        $user = User::firstOrCreate(
            ['email' => 'admin@gesent.local'],
            [
                'name'     => 'Administrateur',
                'password' => Hash::make($password),
            ]
        );
        if ($user->wasRecentlyCreated) {
            $this->command->info(">>> Admin créé : admin@gesent.local / {$password}");
            $this->command->warn(">>> NOTEZ CE MOT DE PASSE, il ne sera plus affiché.");
        }
        $user->assignRole($admin);

        // Taux TVA (Belgique)
        $tauxTva = [
            ['taux' => 0,  'libelle' => 'Exonéré (0%)',       'defaut' => false],
            ['taux' => 6,  'libelle' => 'Taux réduit (6%)',   'defaut' => false],
            ['taux' => 12, 'libelle' => 'Taux intermédiaire (12%)', 'defaut' => false],
            ['taux' => 21, 'libelle' => 'Taux standard (21%)', 'defaut' => true],
        ];
        foreach ($tauxTva as $tva) {
            TauxTva::firstOrCreate(['taux' => $tva['taux']], $tva);
        }

        // Modes de paiement
        $modes = [
            ['nom' => 'Virement bancaire', 'defaut' => true,  'actif' => true],
            ['nom' => 'Carte bancaire',    'defaut' => false, 'actif' => true],
            ['nom' => 'Chèque',            'defaut' => false, 'actif' => true],
            ['nom' => 'Comptant',          'defaut' => false, 'actif' => true],
        ];
        foreach ($modes as $mode) {
            ModePaiement::firstOrCreate(['nom' => $mode['nom']], $mode);
        }
    }
}
