<?php

namespace App\Console\Commands;

use App\Mail\RelanceFacture;
use App\Models\EmailEnvoi;
use App\Models\Facture;
use App\Models\RelanceEtape;
use App\Models\RelanceScenario;
use App\Models\User;
use App\Notifications\FactureEnRetard;
use App\Services\MailConfigService;
use App\Services\MailTemplateService;
use App\States\Facture\EnRetard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

class RelancerFactures extends Command
{
    protected $signature   = 'factures:relancer {--dry-run : Afficher les relances à envoyer sans les envoyer}';
    protected $description = 'Envoyer automatiquement les relances pour les factures en retard';

    // Fallback hardcodé (si aucun scénario en base)
    private const SEUILS_FALLBACK = [
        1 => 7,
        2 => 21,
        3 => 35,
    ];

    public function handle(): int
    {
        $factures = Facture::with('client', 'chantier', 'relanceScenario.etapes')
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

        $scenarioDefaut = RelanceScenario::with('etapes')->where('est_defaut', true)->first();
        $envoyees       = 0;
        $ignorees       = 0;

        foreach ($factures as $facture) {
            $email = $facture->client?->email;

            if (!$email) {
                $this->warn("  {$facture->numero} : client sans email, ignorée.");
                $ignorees++;
                continue;
            }

            $joursRetard = (int) $facture->date_echeance->diffInDays(now());
            $etape       = $this->trouverEtape($facture, $scenarioDefaut, $joursRetard);

            if (!$etape) {
                continue; // délai pas encore atteint ou toutes les étapes épuisées
            }

            if ($this->option('dry-run')) {
                $this->info("[DRY-RUN] Étape {$etape->numero_ordre} (J+{$etape->delai_jours}) → {$facture->numero} ({$email}) — {$joursRetard}j de retard");
                continue;
            }

            MailConfigService::configure();

            $sujet = MailTemplateService::resoudreEtape($etape, $facture, 'sujet');

            try {
                Mail::to($email)->send(new RelanceFacture($facture, $etape));

                EmailEnvoi::create([
                    'document_type' => Facture::class,
                    'document_id'   => $facture->id,
                    'sent_by'       => null,
                    'destinataire'  => $email,
                    'sujet'         => $sujet,
                    'message'       => null,
                    'statut'        => 'envoye',
                    'envoye_at'     => now(),
                ]);

                $niveauRelance = $facture->nb_relances + 1;
                $facture->update([
                    'nb_relances'          => $niveauRelance,
                    'derniere_relance_at'  => now()->toDateString(),
                    'prochaine_relance_at' => now()->addDays(14)->toDateString(),
                ]);

                if (!($facture->statut instanceof EnRetard)) {
                    try {
                        $facture->statut->transitionTo(EnRetard::class);
                    } catch (TransitionNotFound $e) {
                        Log::warning("Relance {$facture->numero} : transition en_retard impossible depuis {$facture->statut}.");
                    }
                }

                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new FactureEnRetard($facture));
                }

                $this->info("  Étape {$etape->numero_ordre} envoyée : {$facture->numero} → {$email}");
                $envoyees++;

            } catch (\Exception $e) {
                $this->error("  Erreur {$facture->numero} : {$e->getMessage()}");
                Log::error('Relance auto échouée', [
                    'facture' => $facture->numero,
                    'email'   => $email,
                    'error'   => $e->getMessage(),
                ]);

                EmailEnvoi::create([
                    'document_type' => Facture::class,
                    'document_id'   => $facture->id,
                    'sent_by'       => null,
                    'destinataire'  => $email,
                    'sujet'         => $sujet,
                    'message'       => null,
                    'statut'        => 'erreur',
                    'erreur'        => $e->getMessage(),
                    'envoye_at'     => now(),
                ]);
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("{$envoyees} relance(s) envoyée(s), {$ignorees} ignorée(s) (sans email).");
        }

        return Command::SUCCESS;
    }

    private function trouverEtape(Facture $facture, ?RelanceScenario $scenarioDefaut, int $joursRetard): ?RelanceEtape
    {
        $scenario = $facture->relanceScenario ?? $scenarioDefaut;

        if ($scenario) {
            // Scénario configuré : trouver la prochaine étape active
            $etapes = $scenario->etapes->where('actif', true)->sortBy('numero_ordre')->values();
            $etape  = $etapes->get($facture->nb_relances);

            if (!$etape || $joursRetard < $etape->delai_jours) {
                return null;
            }

            return $etape;
        }

        // Fallback hardcodé si aucun scénario
        $niveauRelance = $facture->nb_relances + 1;
        $seuilRequis   = self::SEUILS_FALLBACK[min($niveauRelance, 3)];

        if ($joursRetard < $seuilRequis) {
            return null;
        }

        // Créer une RelanceEtape virtuelle (non persistée) pour le fallback
        $sujets = [
            1 => "Rappel — Facture {$facture->numero}",
            2 => "2ème rappel — Facture {$facture->numero} en retard",
            3 => "URGENT — Facture {$facture->numero} impayée",
        ];
        $corps = [
            1 => "Bonjour,\n\nNous nous permettons de vous rappeler que notre facture {$facture->numero} reste impayée.\n\nCordialement,",
            2 => "Bonjour,\n\nSauf erreur de notre part, notre facture {$facture->numero} reste impayée à ce jour.\n\nNous vous remercions de régulariser cette situation.\n\nCordialement,",
            3 => "Bonjour,\n\nMalgré nos rappels précédents, notre facture {$facture->numero} reste impayée.\n\nSans règlement sous 8 jours, nous engagerons une procédure de recouvrement.\n\nCordialement,",
        ];

        $niveau = min($niveauRelance, 3);
        $etape  = new RelanceEtape([
            'numero_ordre' => $niveau,
            'delai_jours'  => $seuilRequis,
            'sujet'        => $sujets[$niveau],
            'corps_email'  => $corps[$niveau],
            'canal'        => $niveau >= 3 ? 'les_deux' : 'mail',
            'ton'          => match($niveau) { 1 => 'cordial', 2 => 'ferme', default => 'formel' },
        ]);

        return $etape;
    }
}
