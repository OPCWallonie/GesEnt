<?php

use App\Http\Controllers\AvenantController;
use App\Http\Controllers\AvoirController;
use App\Http\Controllers\ChargeFonctionnementController;
use App\Http\Controllers\BonCommandeController;
use App\Http\Controllers\ChantierController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FactureAchatController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ExportComptableController;
use App\Http\Controllers\JournalChantierController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StatistiquesController;
use App\Http\Controllers\PeppolDashboardController;
use App\Http\Controllers\PeppolWebhookController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\OuvrierController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ReposCollectifController;
use App\Http\Controllers\RelanceScenariosController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Contraintes globales : tous les paramètres {id} ne peuvent être que numériques,
// ce qui évite que les routes show interceptent les routes /create ou /edit.
Route::patterns([
    'ouvrier'      => '[0-9]+',
    'client'       => '[0-9]+',
    'chantier'     => '[0-9]+',
    'produit'      => '[0-9]+',
    'fournisseur'  => '[0-9]+',
    'devis'        => '[0-9]+',
    'bonCommande'  => '[0-9]+',
    'facture'      => '[0-9]+',
    'factureAchat' => '[0-9]+',
    'avoir'        => '[0-9]+',
    'absence'        => '[0-9]+',
    'kit'            => '[0-9]+',
    'reposCollectif' => '[0-9]+',
]);

Route::get('/', fn() => redirect()->route('dashboard'));

// Webhook Peppol — réception factures entrantes (public, sans CSRF, token custom)
Route::post('/webhook/peppol', [PeppolWebhookController::class, 'handle'])
    ->name('webhook.peppol')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('throttle:30,1');

Route::middleware(['auth'])->group(function () {

    // ──────────────────────────────────────────────────────────────
    // LECTURE — tous les rôles (admin, comptable, lecture)
    // ──────────────────────────────────────────────────────────────

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', SearchController::class)->name('search');

    // Clients
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // Chantiers
    Route::get('chantiers', [ChantierController::class, 'index'])->name('chantiers.index');
    Route::get('chantiers/{chantier}', [ChantierController::class, 'show'])->name('chantiers.show');

    // Produits
    Route::get('produits', [ProduitController::class, 'index'])->name('produits.index');
    Route::get('produits/{produit}', [ProduitController::class, 'show'])->name('produits.show');

    // Devis
    Route::get('devis', [DevisController::class, 'index'])->name('devis.index');
    Route::get('devis/{devis}', [DevisController::class, 'show'])->name('devis.show');
    Route::get('devis/{devis}/pdf', [DevisController::class, 'pdf'])->name('devis.pdf');

    // Bons de commande
    Route::get('bons-commande', [BonCommandeController::class, 'index'])->name('bons-commande.index');
    Route::get('bons-commande/{bonCommande}', [BonCommandeController::class, 'show'])->name('bons-commande.show');
    Route::get('bons-commande/{bonCommande}/pdf', [BonCommandeController::class, 'pdf'])->name('bons-commande.pdf');

    // Factures
    Route::get('factures', [FactureController::class, 'index'])->name('factures.index');
    Route::get('factures/{facture}', [FactureController::class, 'show'])->name('factures.show');
    Route::get('factures/{facture}/pdf', [FactureController::class, 'pdf'])->name('factures.pdf');
    Route::get('factures/{facture}/relance-pdf/{etape}', [FactureController::class, 'relancePdf'])->name('factures.relance-pdf');

    // Avoirs
    Route::get('avoirs/{avoir}', [AvoirController::class, 'show'])->name('avoirs.show');
    Route::get('avoirs/{avoir}/pdf', [AvoirController::class, 'pdf'])->name('avoirs.pdf');

    // Fournisseurs
    Route::get('fournisseurs', [FournisseurController::class, 'index'])->name('fournisseurs.index');
    Route::get('fournisseurs/{fournisseur}', [FournisseurController::class, 'show'])->name('fournisseurs.show');

    // Factures achat
    Route::get('factures-achat', [FactureAchatController::class, 'index'])->name('factures-achat.index');
    Route::get('factures-achat/{factureAchat}', [FactureAchatController::class, 'show'])->name('factures-achat.show');
    Route::get('factures-achat/{factureAchat}/fichier', [FactureAchatController::class, 'fichier'])->name('factures-achat.fichier');

    // Ouvriers
    Route::get('ouvriers', [OuvrierController::class, 'index'])->name('ouvriers.index');
    Route::get('ouvriers/{ouvrier}', [OuvrierController::class, 'show'])->name('ouvriers.show');

    // Pointages
    Route::get('/pointages', [PointageController::class, 'index'])->name('pointages.index');
    Route::get('/pointages/par-chantier', [PointageController::class, 'parChantier'])->name('pointages.par-chantier');

    // Absences
    Route::get('absences', [AbsenceController::class, 'index'])->name('absences.index');

    // Repos compensatoires collectifs (lecture)
    Route::get('repos-collectifs', [ReposCollectifController::class, 'index'])->name('repos-collectifs.index');
    Route::get('repos-collectifs/{reposCollectif}', [ReposCollectifController::class, 'show'])->name('repos-collectifs.show');

    // Catalogue
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

    // Statistiques (consultation — tous les rôles)
    Route::get('/statistiques', [StatistiquesController::class, 'index'])->name('statistiques.index');
    Route::get('/statistiques/balance-agee', [StatistiquesController::class, 'balanceAgee'])->name('statistiques.balance-agee');
    Route::get('/statistiques/tresorerie', [StatistiquesController::class, 'tresorerie'])->name('statistiques.tresorerie');
    Route::get('/statistiques/chantiers', [StatistiquesController::class, 'chantiersRentabilite'])->name('statistiques.chantiers');

    // Peppol dashboard
    Route::get('/peppol', [PeppolDashboardController::class, 'index'])->name('peppol.dashboard');

    // Notifications (chaque utilisateur gère les siennes)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/redirect', [NotificationController::class, 'redirect'])->name('notifications.redirect');
    Route::post('/notifications/marquer-toutes-lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.marquer-toutes-lues');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // Kits API (nécessaire pour composer un devis, lecture seule)
    Route::get('/api/kits', [KitController::class, 'apiList'])->name('kits.api-list');
    Route::get('/api/kits/{kit}/lignes', [KitController::class, 'apiLignes'])->name('kits.api-lignes');

    // API autocomplete — rate limiting (lecture seule, tous rôles)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/api/produits/search', [ProduitController::class, 'search'])->name('produits.search');
        Route::get('/api/produits/suggestions', [ProduitController::class, 'suggestions'])->name('produits.suggestions');
        Route::get('/api/catalog/search', [CatalogController::class, 'search'])->name('catalog.search');
        Route::get('/api/clients/search', [ClientController::class, 'apiSearch'])->name('clients.api-search');
        Route::get('/api/clients/{client}/chantiers', [ClientController::class, 'chantiers'])->name('clients.chantiers');
        Route::get('/api/chantiers/search', [ChantierController::class, 'apiSearch'])->name('chantiers.api-search');
        Route::get('/api/fournisseurs/search', [FournisseurController::class, 'apiSearch'])->name('fournisseurs.api-search');
        Route::get('/api/ouvriers/search', [OuvrierController::class, 'apiSearch'])->name('ouvriers.api-search');
        Route::get('/api/chantiers/{chantier}/coefficient-marge', function (\App\Models\Chantier $chantier) {
            $chantier->load('client');
            return response()->json(['coefficient_marge' => $chantier->coefficientMargeEffectif()]);
        })->name('chantiers.coefficient-marge');
        Route::get('/api/peppol/discovery/{client}', function (\App\Models\Client $client, \App\Services\PeppolService $peppol) {
            if (!$client->numero_tva) {
                return response()->json(['available' => false, 'reason' => 'Pas de numéro de TVA']);
            }
            return response()->json(['available' => $peppol->destinataireDisponible($client->numero_tva)]);
        })->name('api.peppol.discovery');
    });

    // Profil & 2FA (tous les rôles)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::get('/2fa/recovery-codes', [TwoFactorController::class, 'showRecoveryCodes'])->name('2fa.recovery-codes');
    Route::get('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify')
        ->withoutMiddleware(\App\Http\Middleware\TwoFactorMiddleware::class);
    Route::post('/2fa/verify', [TwoFactorController::class, 'verifyCheck'])->name('2fa.verify.check')
        ->withoutMiddleware(\App\Http\Middleware\TwoFactorMiddleware::class);
    Route::post('/2fa/email-code', [TwoFactorController::class, 'sendEmailCode'])->name('2fa.email-code')
        ->withoutMiddleware(\App\Http\Middleware\TwoFactorMiddleware::class);


    // ──────────────────────────────────────────────────────────────
    // ÉCRITURE — admin + comptable uniquement
    // ──────────────────────────────────────────────────────────────

    Route::middleware(['role:admin|comptable'])->group(function () {

        // Clients
        Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/api/clients/quick-create', [ClientController::class, 'quickCreate'])->name('clients.quick-create');

        // Chantiers
        Route::get('chantiers/create', [ChantierController::class, 'create'])->name('chantiers.create');
        Route::post('chantiers', [ChantierController::class, 'store'])->name('chantiers.store');
        Route::get('chantiers/{chantier}/edit', [ChantierController::class, 'edit'])->name('chantiers.edit');
        Route::put('chantiers/{chantier}', [ChantierController::class, 'update'])->name('chantiers.update');
        Route::delete('chantiers/{chantier}', [ChantierController::class, 'destroy'])->name('chantiers.destroy');
        Route::post('/api/clients/{client}/chantiers/quick-create', [ChantierController::class, 'quickCreate'])->name('chantiers.quick-create');
        Route::post('chantiers/{chantier}/journal', [JournalChantierController::class, 'store'])->name('chantiers.journal.store');
        Route::delete('journal/{journal}', [JournalChantierController::class, 'destroy'])->name('chantiers.journal.destroy');

        // Devis
        Route::get('devis/create', [DevisController::class, 'create'])->name('devis.create');
        Route::post('devis', [DevisController::class, 'store'])->name('devis.store');
        Route::get('devis/{devis}/edit', [DevisController::class, 'edit'])->name('devis.edit');
        Route::put('devis/{devis}', [DevisController::class, 'update'])->name('devis.update');
        Route::delete('devis/{devis}', [DevisController::class, 'destroy'])->name('devis.destroy');
        Route::post('devis/{devis}/envoyer', [DevisController::class, 'envoyer'])->name('devis.envoyer');
        Route::post('devis/{devis}/dupliquer', [DevisController::class, 'dupliquer'])->name('devis.dupliquer');
        Route::post('devis/{devis}/convertir-bdc', [DevisController::class, 'convertirEnBdc'])->name('devis.convertir-bdc');
        Route::post('devis/{devis}/sauvegarder-kit', [DevisController::class, 'sauvegarderCommeKit'])->name('devis.sauvegarder-kit');

        // Kits (CRUD complet)
        Route::resource('kits', KitController::class);

        // Bons de commande
        Route::get('bons-commande/create', [BonCommandeController::class, 'create'])->name('bons-commande.create');
        Route::post('bons-commande', [BonCommandeController::class, 'store'])->name('bons-commande.store');
        Route::get('bons-commande/{bonCommande}/edit', [BonCommandeController::class, 'edit'])->name('bons-commande.edit');
        Route::put('bons-commande/{bonCommande}', [BonCommandeController::class, 'update'])->name('bons-commande.update');
        Route::delete('bons-commande/{bonCommande}', [BonCommandeController::class, 'destroy'])->name('bons-commande.destroy');
        Route::post('bons-commande/{bonCommande}/envoyer', [BonCommandeController::class, 'envoyer'])->name('bons-commande.envoyer');
        Route::post('bons-commande/{bonCommande}/facturer', [BonCommandeController::class, 'facturer'])->name('bons-commande.facturer');

        // Avenants
        Route::resource('bons-commande.avenants', AvenantController::class)
            ->parameters(['bons-commande' => 'bonCommande'])
            ->shallow()
            ->except(['index']);

        // Factures
        Route::get('factures/create', [FactureController::class, 'create'])->name('factures.create');
        Route::post('factures', [FactureController::class, 'store'])->name('factures.store');
        Route::get('factures/{facture}/edit', [FactureController::class, 'edit'])->name('factures.edit');
        Route::put('factures/{facture}', [FactureController::class, 'update'])->name('factures.update');
        Route::delete('factures/{facture}', [FactureController::class, 'destroy'])->name('factures.destroy');
        Route::post('factures/{facture}/envoyer', [FactureController::class, 'envoyer'])->name('factures.envoyer');
        Route::post('factures/{facture}/envoyer-peppol', [FactureController::class, 'envoyerPeppol'])->name('factures.envoyer-peppol');
        Route::post('factures/envoyer-peppol-masse', [FactureController::class, 'envoyerPeppolEnMasse'])->name('factures.envoyer-peppol-masse');
        Route::patch('factures/{facture}/marquer-payee', [FactureController::class, 'marquerPayee'])->name('factures.marquer-payee');
        Route::patch('factures/{facture}/relancer', [FactureController::class, 'relancer'])->name('factures.relancer');
        Route::patch('factures/{facture}/liberer-retenue', [FactureController::class, 'libererRetenue'])->name('factures.liberer-retenue');
        Route::patch('factures/{facture}/toggle-relance-auto', [FactureController::class, 'toggleRelanceAuto'])->name('factures.toggle-relance-auto');
        Route::patch('factures/{facture}/scenario-relance', [FactureController::class, 'changerScenarioRelance'])->name('factures.scenario-relance');
        Route::post('factures/{facture}/sync-odoo', [FactureController::class, 'syncOdoo'])->name('factures.sync-odoo');

        // Avoirs
        Route::get('factures/{facture}/avoirs/create', [AvoirController::class, 'create'])->name('avoirs.create');
        Route::post('factures/{facture}/avoirs', [AvoirController::class, 'store'])->name('avoirs.store');
        Route::delete('avoirs/{avoir}', [AvoirController::class, 'destroy'])->name('avoirs.destroy');
        Route::post('avoirs/{avoir}/envoyer-peppol', [AvoirController::class, 'envoyerPeppol'])->name('avoirs.envoyer-peppol');

        // Factures achat
        Route::get('factures-achat/create', [FactureAchatController::class, 'create'])->name('factures-achat.create');
        Route::post('factures-achat', [FactureAchatController::class, 'store'])->name('factures-achat.store');
        Route::get('factures-achat/{factureAchat}/edit', [FactureAchatController::class, 'edit'])->name('factures-achat.edit');
        Route::put('factures-achat/{factureAchat}', [FactureAchatController::class, 'update'])->name('factures-achat.update');
        Route::delete('factures-achat/{factureAchat}', [FactureAchatController::class, 'destroy'])->name('factures-achat.destroy');
        Route::patch('factures-achat/{factureAchat}/marquer-payee', [FactureAchatController::class, 'marquerPayee'])->name('factures-achat.marquer-payee');

        // OCR — rate limit strict (appels API payants)
        Route::post('/ocr/extract', [OcrController::class, 'extract'])
            ->name('ocr.extract')
            ->middleware('throttle:10,1');

        // Produits
        Route::get('produits/create', [ProduitController::class, 'create'])->name('produits.create');
        Route::post('produits', [ProduitController::class, 'store'])->name('produits.store');
        Route::get('produits/{produit}/edit', [ProduitController::class, 'edit'])->name('produits.edit');
        Route::put('produits/{produit}', [ProduitController::class, 'update'])->name('produits.update');
        Route::delete('produits/{produit}', [ProduitController::class, 'destroy'])->name('produits.destroy');
        Route::post('produits/import', [ProduitController::class, 'import'])->name('produits.import');

        // Fournisseurs
        Route::get('fournisseurs/create', [FournisseurController::class, 'create'])->name('fournisseurs.create');
        Route::post('fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store');
        Route::get('fournisseurs/{fournisseur}/edit', [FournisseurController::class, 'edit'])->name('fournisseurs.edit');
        Route::put('fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update');
        Route::delete('fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy');
        Route::post('/api/fournisseurs/quick-create', [FournisseurController::class, 'quickCreate'])->name('fournisseurs.quick-create');

        // Catalogue
        Route::post('/catalog/import', [CatalogController::class, 'import'])->name('catalog.import');
        Route::post('/catalog/sync', [CatalogController::class, 'sync'])->name('catalog.sync');
        Route::post('/catalog/config', [CatalogController::class, 'updateConfig'])->name('catalog.config');
        Route::post('/catalog/vider', [CatalogController::class, 'vider'])->name('catalog.vider');
        Route::delete('/catalog/config', [CatalogController::class, 'deleteConfig'])->name('catalog.config.delete');

        // Charges fixes & frais généraux
        Route::resource('charges-fonctionnement', ChargeFonctionnementController::class)
            ->except('show');

        // Ouvriers
        Route::get('ouvriers/create', [OuvrierController::class, 'create'])->name('ouvriers.create');
        Route::post('ouvriers', [OuvrierController::class, 'store'])->name('ouvriers.store');
        Route::get('ouvriers/{ouvrier}/edit', [OuvrierController::class, 'edit'])->name('ouvriers.edit');
        Route::put('ouvriers/{ouvrier}', [OuvrierController::class, 'update'])->name('ouvriers.update');
        Route::delete('ouvriers/{ouvrier}', [OuvrierController::class, 'destroy'])->name('ouvriers.destroy');

        // Pointages
        Route::post('/pointages', [PointageController::class, 'store'])->name('pointages.store');
        Route::post('/pointages/copier-semaine', [PointageController::class, 'copier'])->name('pointages.copier');
        Route::delete('/pointages/{pointage}', [PointageController::class, 'destroy'])->name('pointages.destroy');

        // Absences
        Route::get('absences/create', [AbsenceController::class, 'create'])->name('absences.create');
        Route::post('absences', [AbsenceController::class, 'store'])->name('absences.store');
        Route::get('absences/{absence}/edit', [AbsenceController::class, 'edit'])->name('absences.edit');
        Route::put('absences/{absence}', [AbsenceController::class, 'update'])->name('absences.update');
        Route::delete('absences/{absence}', [AbsenceController::class, 'destroy'])->name('absences.destroy');

        // Repos compensatoires collectifs (écriture)
        Route::get('repos-collectifs/create', [ReposCollectifController::class, 'create'])->name('repos-collectifs.create');
        Route::post('repos-collectifs', [ReposCollectifController::class, 'store'])->name('repos-collectifs.store');
        Route::post('repos-collectifs/{reposCollectif}/appliquer', [ReposCollectifController::class, 'appliquer'])->name('repos-collectifs.appliquer');
        Route::post('repos-collectifs/{reposCollectif}/annuler', [ReposCollectifController::class, 'annuler'])->name('repos-collectifs.annuler');
        Route::delete('repos-collectifs/{reposCollectif}', [ReposCollectifController::class, 'destroy'])->name('repos-collectifs.destroy');
        Route::get('repos-collectifs/importer', [ReposCollectifController::class, 'importerForm'])->name('repos-collectifs.importer');
        Route::post('repos-collectifs/importer', [ReposCollectifController::class, 'importer'])->name('repos-collectifs.importer.post');

        // Exports
        Route::get('/export/factures', [ExportController::class, 'factures'])->name('export.factures');
        Route::get('/export/factures-achat', [ExportController::class, 'facturesAchat'])->name('export.factures-achat');
        Route::get('/export/devis', [ExportController::class, 'devis'])->name('export.devis');
        Route::get('/export/factures-pdf-zip', [ExportController::class, 'facturesPdfZip'])->name('export.factures-pdf-zip');
        Route::get('/export-comptable', [ExportComptableController::class, 'index'])->name('export-comptable.index');
        Route::post('/export-comptable', [ExportComptableController::class, 'export'])->name('export-comptable.export');

        // Fichiers RH protégés (certificats, justificatifs)
        Route::get('/fichiers/rh/{chemin}', function (string $chemin) {
            return \App\Services\FileUploadService::servir('rh/' . $chemin);
        })->where('chemin', '.*')->name('fichiers.rh');
    });


    // ──────────────────────────────────────────────────────────────
    // ADMIN uniquement
    // ──────────────────────────────────────────────────────────────

    Route::middleware(['role:admin'])->group(function () {

        // Utilisateurs
        Route::resource('users', UserController::class)->except('show');

        // Paramètres
        Route::get('/parametres', [ParametresController::class, 'edit'])->name('parametres.edit');
        Route::put('/parametres', [ParametresController::class, 'update'])->name('parametres.update');
        Route::post('/parametres/tester-odoo', [ParametresController::class, 'testerOdoo'])->name('parametres.tester-odoo');
        Route::post('/parametres/tester-email', [ParametresController::class, 'testerEmail'])->name('parametres.tester-email');
        Route::post('/odoo/test', function (Request $request, \App\Services\OdooService $odoo) {
            $saved  = \App\Models\ParametresEntreprise::instance();
            $apiKey = $request->filled('api_key')
                ? $request->input('api_key')
                : ($saved->odoo_api_key_decrypte ?? '');
            $odoo->reconfigurer(
                $request->input('url', ''),
                $request->input('database', ''),
                $request->input('username', ''),
                $apiKey,
            );
            return response()->json($odoo->testerConnexion());
        })->name('odoo.test');

        // Scénarios de relance
        Route::resource('relance-scenarios', RelanceScenariosController::class)
            ->parameters(['relance-scenarios' => 'relanceScenario']);
        Route::post('relance-scenarios/{relanceScenario}/definir-defaut', [RelanceScenariosController::class, 'definirDefaut'])
            ->name('relance-scenarios.definir-defaut');
        Route::get('relance-scenarios/{relanceScenario}/apercu/{etape}', [RelanceScenariosController::class, 'apercu'])
            ->name('relance-scenarios.apercu');
    });
});

require __DIR__.'/auth.php';
