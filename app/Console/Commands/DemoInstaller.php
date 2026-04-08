<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoInstaller extends Command
{
    protected $signature = 'demo:install {--fresh : Réinitialiser complètement la base}';
    protected $description = 'Installer la base de démonstration Gesent';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->warn('Réinitialisation complète de la base de données...');
            $this->call('migrate:fresh');
        }

        $this->info('Chargement des données de base...');
        $this->call('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->info('Chargement des données de démonstration...');
        $this->call('db:seed', ['--class' => 'DemoSeeder']);

        $this->info('Initialisation des statistiques de produits...');
        $this->call('produits:init-usage');

        $this->newLine();
        $this->info('=== DÉMONSTRATION GESENT INSTALLÉE ===');
        $this->table(
            ['Compte', 'Email', 'Mot de passe', 'Rôle'],
            [
                ['Admin', 'demo@gesent.be', 'Demo2026!', 'admin'],
                ['Comptable', 'comptable@gesent.be', 'Demo2026!', 'comptable'],
                ['Lecture', 'lecture@gesent.be', 'Demo2026!', 'lecture'],
            ]
        );

        return Command::SUCCESS;
    }
}
