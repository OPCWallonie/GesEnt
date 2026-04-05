<?php

namespace App\Console\Commands;

use App\Models\Facture;
use App\States\Facture\EnRetard;
use Illuminate\Console\Command;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

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

        $count   = 0;
        $ignored = 0;
        foreach ($factures as $facture) {
            try {
                $facture->statut->transitionTo(EnRetard::class);
                $count++;
            } catch (TransitionNotFound $e) {
                $this->warn("  {$facture->numero} : transition vers en_retard impossible depuis {$facture->statut}.");
                $ignored++;
            }
        }

        $this->info("{$count} facture(s) passée(s) en retard" . ($ignored ? ", {$ignored} ignorée(s)." : '.'));
        return Command::SUCCESS;
    }
}
