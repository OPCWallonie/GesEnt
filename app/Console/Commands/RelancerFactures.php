<?php

namespace App\Console\Commands;

use App\Mail\RelanceFacture;
use App\Models\Facture;
use App\Models\User;
use App\Notifications\FactureEnRetard;
use App\States\Facture\EnRetard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

class RelancerFactures extends Command
{
    protected $signature   = 'factures:relancer {--dry-run : Afficher les relances à envoyer sans les envoyer}';
    protected $description = 'Envoyer automatiquement les relances pour les factures en retard';

    // Délais minimums (jours après l'échéance) avant chaque niveau de relance
    private const SEUILS_RELANCE = [
        1 => 7,   // 1ère relance : 7 jours après l'échéance
        2 => 21,  // 2ème relance : 21 jours après l'échéance
        3 => 35,  // 3ème relance et au-delà : 35 jours
    ];

    public function handle(): int
    {
        $factures = Facture::with('client')
            ->whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
            ->where('relance_auto', true)
            ->whereNotNull('date_echeance')
            ->where('date_echeance', '<', now())
            ->where(function ($q) {
                $q->whereNull('prochaine_relance_at')
                  ->orWhere('prochaine_relance_at', '<=', now());
            })
            ->get();

        if ($factures->isEmpty()) {
            $this->info('Aucune facture éligible à la relance.');
            return Command::SUCCESS;
        }

        $envoyees = 0;
        $ignorees = 0;

        foreach ($factures as $facture) {
            $niveauRelance = $facture->nb_relances + 1;
            $email         = $facture->client?->email;

            if (!$email) {
                $this->warn("  {$facture->numero} : client sans email, ignorée.");
                $ignorees++;
                continue;
            }

            // Vérifier que le délai minimal est atteint
            $joursRetard  = (int) $facture->date_echeance->diffInDays(now());
            $seuilRequis  = self::SEUILS_RELANCE[min($niveauRelance, 3)];

            if ($joursRetard < $seuilRequis) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->info("[DRY-RUN] Relance n°{$niveauRelance} → {$facture->numero} ({$email}) — {$joursRetard}j de retard");
                continue;
            }

            try {
                Mail::to($email)->send(new RelanceFacture($facture, min($niveauRelance, 3)));

                $facture->update([
                    'nb_relances'          => $niveauRelance,
                    'derniere_relance_at'  => now()->toDateString(),
                    'prochaine_relance_at' => now()->addDays(14)->toDateString(),
                ]);

                // Transition vers en_retard via la machine à états (idempotente)
                if (!($facture->statut instanceof EnRetard)) {
                    try {
                        $facture->statut->transitionTo(EnRetard::class);
                    } catch (TransitionNotFound $e) {
                        // Déjà dans un état incompatible, log sans interrompre
                        Log::warning("Relance {$facture->numero} : transition en_retard impossible depuis {$facture->statut}.");
                    }
                }

                // Notifier les admins en interne
                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new FactureEnRetard($facture));
                }

                $this->info("  Relance n°{$niveauRelance} envoyée : {$facture->numero} → {$email}");
                $envoyees++;

            } catch (\Exception $e) {
                $this->error("  Erreur {$facture->numero} : {$e->getMessage()}");
                Log::error('Relance auto échouée', [
                    'facture' => $facture->numero,
                    'email'   => $email,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("{$envoyees} relance(s) envoyée(s), {$ignorees} ignorée(s) (sans email).");
        }

        return Command::SUCCESS;
    }
}
