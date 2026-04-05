<?php

namespace App\Console\Commands;

use App\Models\Devis;
use App\Models\Facture;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RefreshStatuts extends Command
{
    protected $signature   = 'gesent:refresh-statuts';
    protected $description = 'Expire les devis périmés et marque les factures en retard';

    public function handle(): int
    {
        $today = Carbon::today();

        $devisExpires = Devis::whereIn('statut', ['brouillon', 'en_attente'])
            ->whereNotNull('date_validite')
            ->where('date_validite', '<', $today)
            ->update(['statut' => 'expire']);

        $facturesEnRetard = Facture::whereIn('statut', ['en_attente', 'envoyee'])
            ->whereNotNull('date_echeance')
            ->where('date_echeance', '<', $today)
            ->update(['statut' => 'en_retard']);

        $this->info("✓ {$devisExpires} devis expirés.");
        $this->info("✓ {$facturesEnRetard} factures marquées en retard.");

        return self::SUCCESS;
    }
}
