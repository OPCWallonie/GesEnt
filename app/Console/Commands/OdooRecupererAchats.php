<?php

namespace App\Console\Commands;

use App\Services\OdooService;
use App\Services\OdooSyncService;
use Illuminate\Console\Command;

class OdooRecupererAchats extends Command
{
    protected $signature = 'odoo:sync-achats';
    protected $description = 'Récupérer les factures fournisseurs depuis Odoo';

    public function handle(OdooSyncService $sync): int
    {
        if (!app(OdooService::class)->isConfigured()) {
            $this->warn('Odoo non configuré.');
            return Command::SUCCESS;
        }

        $result = $sync->recupererFacturesAchat();
        $this->info($result['message']);
        return Command::SUCCESS;
    }
}
