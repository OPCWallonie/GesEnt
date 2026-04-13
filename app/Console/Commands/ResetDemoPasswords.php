<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetDemoPasswords extends Command
{
    protected $signature   = 'users:reset-demo-passwords';
    protected $description = 'Réinitialise les mots de passe des comptes démo avec des valeurs aléatoires';

    public function handle(): int
    {
        $emails = [
            'admin@gesent.local',
            'demo@gesent.be',
            'comptable@gesent.be',
            'lecture@gesent.be',
        ];

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->warn("Compte introuvable : {$email}");
                continue;
            }

            $password = Str::random(16);
            $user->update(['password' => Hash::make($password)]);
            $this->info("{$email} → {$password}");
        }

        $this->warn('NOTEZ CES MOTS DE PASSE. Ils ne seront plus affichés.');
        return Command::SUCCESS;
    }
}
