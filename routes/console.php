<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tous les jours à 1h du matin : expire les devis périmés, marque les factures en retard
Schedule::command('gesent:refresh-statuts')->dailyAt('01:00');

// Tous les jours à 3h du matin : synchronise les catalogues fournisseurs via API
Schedule::command('gesent:sync-catalog')->dailyAt('03:00');

// Toutes les heures : envoie les notifications in-app (factures en retard, devis expirants)
Schedule::command('gesent:envoyer-notifications')->hourly();

// Tous les jours à 8h : envoie les relances email automatiques aux clients en retard
Schedule::command('factures:relancer')->dailyAt('08:00');
