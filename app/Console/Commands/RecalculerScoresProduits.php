<?php

namespace App\Console\Commands;

use App\Services\ProduitUsageService;
use Illuminate\Console\Command;

class RecalculerScoresProduits extends Command
{
    protected $signature   = 'produits:recalculer-scores';
    protected $description = 'Recalculer les scores de fréquence d\'utilisation des produits';

    public function handle(ProduitUsageService $service): int
    {
        $count = $service->recalculerTousLesScores();
        $this->info("{$count} score(s) recalculé(s).");
        return Command::SUCCESS;
    }
}
