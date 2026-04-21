<?php

namespace App\Console\Commands;

use App\Models\CatalogProduit;
use App\Services\Catalog\Volatilite\VolatiliteService;
use Illuminate\Console\Command;

class RecalculerVolatilite extends Command
{
    protected $signature = 'gesent:volatilite:recalculer-tous
                            {--passe=2 : Nombre de passes (1 ou 2). Deux passes recommandées au premier lancement pour initialiser les médianes de groupe.}';

    protected $description = 'Recalcule les indicateurs de volatilité pour tous les produits du catalogue';

    public function handle(VolatiliteService $service): int
    {
        $passes = max(1, (int) $this->option('passe'));
        $total  = CatalogProduit::count();

        if ($total === 0) {
            $this->info('Aucun produit dans le catalogue.');
            return self::SUCCESS;
        }

        $this->info("Catalogue : {$total} produits, {$passes} passe(s) prévue(s).");

        if ($passes >= 2) {
            $this->line('  Passe 1 : calcul des indicateurs (médianes pas encore disponibles).');
            $this->line('  Passe 2 : recalcul des signaux avec médianes de groupe maintenant connues.');
        }

        for ($p = 1; $p <= $passes; $p++) {
            $this->info("Passe {$p}/{$passes}...");
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            CatalogProduit::chunk(200, function ($produits) use ($service, $bar) {
                $service->recalculerProduits($produits);
                $bar->advance($produits->count());
            });

            $bar->finish();
            $this->newLine();
        }

        $this->info('Terminé.');
        return self::SUCCESS;
    }
}
