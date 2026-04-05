<?php

namespace App\Console\Commands;

use App\Models\CatalogConfig;
use App\Services\Catalog\ApiCatalogService;
use Illuminate\Console\Command;

class SyncCatalog extends Command
{
    protected $signature   = 'gesent:sync-catalog {fournisseur? : desco, vanmarke, ou tous si omis}';
    protected $description = 'Synchronise les catalogues fournisseurs via leurs APIs B2B';

    public function handle(ApiCatalogService $service): int
    {
        $cible = $this->argument('fournisseur');

        $configs = CatalogConfig::where('actif', true)
            ->when($cible, fn($q) => $q->where('fournisseur', $cible))
            ->get();

        if ($configs->isEmpty()) {
            $this->warn('Aucun fournisseur actif trouvé. Configurez les accès dans Paramètres > Catalogues.');
            return self::SUCCESS;
        }

        foreach ($configs as $config) {
            $this->info("Synchronisation {$config->nom_affichage}...");

            $resultat = match ($config->fournisseur) {
                'desco'    => $service->syncDesco($config),
                'vanmarke' => $service->syncVanMarke($config),
                default    => $config->url_api
                    ? $service->syncGenerique($config)
                    : ['erreur' => "Pas d'URL API pour {$config->fournisseur}. Utilisez l'import CSV."],
            };

            if (isset($resultat['erreur'])) {
                $this->error("  ✗ {$resultat['erreur']}");
            } else {
                $this->info("  ✓ {$resultat['inseres']} insérés, {$resultat['mis_a_jour']} mis à jour");
            }
        }

        return self::SUCCESS;
    }
}
