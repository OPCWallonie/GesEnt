<?php

namespace App\Console\Commands;

use App\Models\Facture;
use Illuminate\Console\Command;

class DetecterFacturesEnRetard extends Command
{
    protected $signature   = 'factures:detecter-retard';
    protected $description = 'Passer en statut "en_retard" les factures dont l\'échéance est dépassée';

    public function handle(): int
    {
        $factures = Facture::whereIn('statut', ['en_attente', 'envoyee'])
            ->whereNotNull('date_echeance')
            ->where('date_echeance', '<', now())
            ->get();

        $count = 0;
        foreach ($factures as $facture) {
            $facture->update(['statut' => 'en_retard']);
            $count++;
        }

        $this->info("{$count} facture(s) passée(s) en retard.");
        return Command::SUCCESS;
    }
}
