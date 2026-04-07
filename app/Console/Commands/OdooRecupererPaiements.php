<?php

namespace App\Console\Commands;

use App\Services\OdooService;
use App\Services\OdooSyncService;
use Illuminate\Console\Command;

class OdooRecupererPaiements extends Command
{
    protected $signature = 'odoo:sync-paiements';
    protected $description = 'Récupérer les paiements enregistrés dans Odoo';

    public function handle(OdooSyncService $sync): int
    {
        if (!app(OdooService::class)->isConfigured()) {
            $this->warn('Odoo non configuré.');
            return Command::SUCCESS;
        }

        $result = $sync->recupererPaiements();
        $this->info($result['message']);
        return Command::SUCCESS;
    }
}
