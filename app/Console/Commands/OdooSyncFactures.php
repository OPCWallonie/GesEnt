<?php

namespace App\Console\Commands;

use App\Models\Facture;
use App\Services\OdooService;
use App\Services\OdooSyncService;
use Illuminate\Console\Command;

class OdooSyncFactures extends Command
{
    protected $signature = 'odoo:sync-factures {--dry-run}';
    protected $description = 'Synchroniser les factures de vente non encore envoyées vers Odoo';

    public function handle(OdooSyncService $sync): int
    {
        if (!app(OdooService::class)->isConfigured()) {
            $this->warn('Odoo non configuré. Ignoré.');
            return Command::SUCCESS;
        }

        $factures = Facture::whereNull('odoo_move_id')
            ->whereIn('statut', ['en_attente', 'envoyee', 'payee', 'en_retard'])
            ->with('client', 'lignes')
            ->get();

        $this->info("{$factures->count()} facture(s) à synchroniser.");

        foreach ($factures as $facture) {
            if ($this->option('dry-run')) {
                $this->line("  [DRY RUN] {$facture->numero} → {$facture->client->nom}");
                continue;
            }

            $result = $sync->syncFacture($facture);
            $this->line("  {$facture->numero} : {$result['message']}");
            usleep(200000);
        }

        return Command::SUCCESS;
    }
}
