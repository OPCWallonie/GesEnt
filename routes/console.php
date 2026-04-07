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

// Tous les jours à 7h : détecte et marque les factures en retard de paiement
Schedule::command('factures:detecter-retard')->dailyAt('07:00');

// Tous les jours à 8h : envoie les relances email automatiques aux clients en retard
Schedule::command('factures:relancer')->dailyAt('08:00');

// Chaque lundi à 2h du matin : recalculer les scores de fréquence produits
Schedule::command('produits:recalculer-scores')->weeklyOn(1, '02:00');

// Odoo — toutes les 15 min : importer paiements et factures d'achat
Schedule::command('odoo:sync-paiements')->everyFifteenMinutes();
Schedule::command('odoo:sync-achats')->everyFifteenMinutes();

// Odoo — toutes les heures : pousser les factures de vente non synchronisées
Schedule::command('odoo:sync-factures')->hourly();
