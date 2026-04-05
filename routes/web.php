<?php

use App\Http\Controllers\AvenantController;
use App\Http\Controllers\AvoirController;
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
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Recherche globale (toutes les routes auth)
    Route::get('/search', SearchController::class)->name('search');

    // Gestion
    Route::resource('clients', ClientController::class);
    Route::resource('chantiers', ChantierController::class);
    Route::resource('produits', ProduitController::class);
    Route::post('produits/import', [ProduitController::class, 'import'])->name('produits.import');

    // Documents — Devis
    Route::resource('devis', DevisController::class);
    Route::post('devis/{devis}/convertir-bdc', [DevisController::class, 'convertirEnBdc'])->name('devis.convertir-bdc');
    Route::post('devis/{devis}/envoyer', [DevisController::class, 'envoyer'])->name('devis.envoyer');
    Route::get('devis/{devis}/pdf', [DevisController::class, 'pdf'])->name('devis.pdf');

    // Bons de commande
    Route::resource('bons-commande', BonCommandeController::class);
    Route::post('bons-commande/{bonCommande}/facturer', [BonCommandeController::class, 'facturer'])->name('bons-commande.facturer');
    Route::get('bons-commande/{bonCommande}/pdf', [BonCommandeController::class, 'pdf'])->name('bons-commande.pdf');

    // Avenants (sous-ressource de bons-commande)
    Route::resource('bons-commande.avenants', AvenantController::class)->shallow()->except(['index']);

    // Factures
    Route::resource('factures', FactureController::class);
    Route::get('factures/{facture}/pdf', [FactureController::class, 'pdf'])->name('factures.pdf');
    Route::post('factures/{facture}/envoyer', [FactureController::class, 'envoyer'])->name('factures.envoyer');
    Route::patch('factures/{facture}/marquer-payee', [FactureController::class, 'marquerPayee'])->name('factures.marquer-payee');
    Route::patch('factures/{facture}/relancer', [FactureController::class, 'relancer'])->name('factures.relancer');
    Route::patch('factures/{facture}/liberer-retenue', [FactureController::class, 'libererRetenue'])->name('factures.liberer-retenue');

    // Avoirs
    Route::get('factures/{facture}/avoirs/create', [AvoirController::class, 'create'])->name('avoirs.create');
    Route::post('factures/{facture}/avoirs', [AvoirController::class, 'store'])->name('avoirs.store');
    Route::get('avoirs/{avoir}', [AvoirController::class, 'show'])->name('avoirs.show');
    Route::delete('avoirs/{avoir}', [AvoirController::class, 'destroy'])->name('avoirs.destroy');
    Route::get('avoirs/{avoir}/pdf', [AvoirController::class, 'pdf'])->name('avoirs.pdf');

    // API autocomplete
    Route::get('/api/produits/search', [ProduitController::class, 'search'])->name('produits.search');
    Route::get('/api/catalog/search', [CatalogController::class, 'search'])->name('catalog.search');
    Route::get('/api/clients/{client}/chantiers', [ClientController::class, 'chantiers'])->name('clients.chantiers');

    // Catalogue fournisseurs
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalog/import', [CatalogController::class, 'import'])->name('catalog.import');
    Route::post('/catalog/sync', [CatalogController::class, 'sync'])->name('catalog.sync');
    Route::post('/catalog/config', [CatalogController::class, 'updateConfig'])->name('catalog.config');
    Route::post('/catalog/vider', [CatalogController::class, 'vider'])->name('catalog.vider');
    Route::delete('/catalog/config', [CatalogController::class, 'deleteConfig'])->name('catalog.config.delete');

    // OCR Factures (extraction IA)
    Route::post('/ocr/extract', [OcrController::class, 'extract'])->name('ocr.extract');

    // Fournisseurs & Factures d'achats
    Route::resource('fournisseurs', FournisseurController::class);
    Route::resource('factures-achat', FactureAchatController::class);
    Route::patch('factures-achat/{facturesAchat}/marquer-payee', [FactureAchatController::class, 'marquerPayee'])->name('factures-achat.marquer-payee');

    // Statistiques & Exports — comptable + admin
    Route::middleware(['role:admin|comptable'])->group(function () {
        Route::get('/statistiques', [StatistiquesController::class, 'index'])->name('statistiques.index');
        Route::get('/export/factures',       [ExportController::class, 'factures'])->name('export.factures');
        Route::get('/export/factures-achat', [ExportController::class, 'facturesAchat'])->name('export.factures-achat');
        Route::get('/export/devis',          [ExportController::class, 'devis'])->name('export.devis');
    });

    // Gestion des utilisateurs — admin uniquement
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });

    // Paramètres — admin uniquement
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/parametres', [ParametresController::class, 'edit'])->name('parametres.edit');
        Route::put('/parametres', [ParametresController::class, 'update'])->name('parametres.update');
    });

    // Journal de chantier
    Route::post('chantiers/{chantier}/journal', [JournalChantierController::class, 'store'])->name('chantiers.journal.store');
    Route::delete('journal/{journal}', [JournalChantierController::class, 'destroy'])->name('chantiers.journal.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/redirect', [NotificationController::class, 'redirect'])->name('notifications.redirect');
    Route::post('/notifications/marquer-toutes-lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.marquer-toutes-lues');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // Export comptable — admin + comptable
    Route::middleware(['role:admin|comptable'])->group(function () {
        Route::get('/export-comptable', [ExportComptableController::class, 'index'])->name('export-comptable.index');
        Route::post('/export-comptable', [ExportComptableController::class, 'export'])->name('export-comptable.export');
    });

    // Profil utilisateur (tous les rôles)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
