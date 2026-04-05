<?php

namespace App\Console\Commands;

use App\Models\Devis;
use App\Models\Facture;
use App\Models\User;
use App\Notifications\DevisExpireBientot;
use App\Notifications\FactureEnRetard;
use Illuminate\Console\Command;

class EnvoyerNotifications extends Command
{
    protected $signature   = 'gesent:envoyer-notifications';
    protected $description = 'Génère les notifications in-app (factures en retard, devis qui expirent)';

    public function handle(): int
    {
        $admins = User::role(['admin', 'comptable'])->get();
        if ($admins->isEmpty()) return self::SUCCESS;

        // Factures en retard (passées à l'état en_retard ou échues)
        $facturesEnRetard = Facture::with('client')
            ->whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
            ->where('date_echeance', '<', now())
            ->get();

        $nbFactures = 0;
        foreach ($facturesEnRetard as $facture) {
            // Ne notifier qu'une fois par jour : vérifier si notif existante < 24h
            $dejaNotifie = $admins->first()->notifications()
                ->where('type', \App\Notifications\FactureEnRetard::class)
                ->where('data->url', route('factures.show', $facture))
                ->where('created_at', '>', now()->subDay())
                ->exists();

            if (!$dejaNotifie) {
                foreach ($admins as $admin) {
                    $admin->notify(new FactureEnRetard($facture));
                }
                $nbFactures++;
            }
        }

        // Devis expirant dans les 3 prochains jours
        $devisExpirant = Devis::with('client')
            ->whereIn('statut', ['en_attente', 'valide'])
            ->whereBetween('date_validite', [now(), now()->addDays(3)])
            ->get();

        $nbDevis = 0;
        foreach ($devisExpirant as $devis) {
            $dejaNotifie = $admins->first()->notifications()
                ->where('type', \App\Notifications\DevisExpireBientot::class)
                ->where('data->url', route('devis.show', $devis))
                ->where('created_at', '>', now()->subDay())
                ->exists();

            if (!$dejaNotifie) {
                foreach ($admins as $admin) {
                    $admin->notify(new DevisExpireBientot($devis));
                }
                $nbDevis++;
            }
        }

        $this->info("Notifications envoyées : {$nbFactures} factures en retard, {$nbDevis} devis expirants.");
        return self::SUCCESS;
    }
}
