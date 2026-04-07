<?php

namespace App\Console\Commands;

use App\Services\ProduitUsageService;
use Illuminate\Console\Command;

class InitialiserUsageProduits extends Command
{
    protected $signature   = 'produits:init-usage';
    protected $description = 'Initialiser les statistiques d\'utilisation depuis l\'historique des documents';

    public function handle(ProduitUsageService $service): int
    {
        $this->info('Analyse de l\'historique...');
        $result = $service->initialiserDepuisHistorique();
        $this->info("{$result['produits']} produit(s) analysé(s).");
        return Command::SUCCESS;
    }
}
