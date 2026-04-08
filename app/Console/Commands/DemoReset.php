<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoReset extends Command
{
    protected $signature = 'demo:reset {--confirm : Confirmer la suppression}';
    protected $description = 'Supprimer toutes les données et repartir à zéro (base vide)';

    public function handle(): int
    {
        if (!$this->option('confirm')) {
            if (!$this->confirm('ATTENTION : cette opération supprime TOUTES les données. Continuer ?')) {
                return Command::SUCCESS;
            }
        }

        $this->warn('Suppression de toutes les données...');
        $this->call('migrate:fresh');
        $this->call('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->info('Base réinitialisée. Seuls les rôles, taux TVA et modes de paiement sont conservés.');
        $this->info('Créez votre premier utilisateur via la commande tinker ou via php artisan demo:install.');

        return Command::SUCCESS;
    }
}
