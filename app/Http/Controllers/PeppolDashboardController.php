<?php

namespace App\Http\Controllers;

use App\Models\Avoir;
use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\ParametresEntreprise;
use App\Models\PeppolWebhookLog;

class PeppolDashboardController extends Controller
{
    public function index()
    {
        $params = ParametresEntreprise::instance();

        $facturesTotal    = Facture::whereIn('statut', ['en_attente', 'envoyee', 'payee', 'en_retard'])->count();
        $facturesPeppol   = Facture::whereNotNull('peppol_envoye_at')->count();
        $facturesAEnvoyer = Facture::whereNull('peppol_envoye_at')
            ->whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
            ->whereHas('client', fn($q) => $q->whereNotNull('numero_tva'))
            ->count();
        $facturesSansTva  = Facture::whereNull('peppol_envoye_at')
            ->whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
            ->whereHas('client', fn($q) => $q->whereNull('numero_tva'))
            ->count();

        $avoirsTotal  = Avoir::count();
        $avoirsPeppol = Avoir::whereNotNull('peppol_envoye_at')->count();

        $achatsTotal  = FactureAchat::count();
        $achatsPeppol = FactureAchat::where('peppol_source', 'peppol')->count();
        $achatsOcr    = FactureAchat::where('peppol_source', 'ocr')->count();
        $achatsManuel = FactureAchat::where('peppol_source', 'manuel')->count();

        $webhookLogs   = PeppolWebhookLog::orderByDesc('created_at')->take(20)->get();
        $webhookErrors = PeppolWebhookLog::where('status', 'failed')->count();

        $dernieresReceptions = FactureAchat::with('fournisseur')
            ->where('peppol_source', 'peppol')
            ->orderByDesc('peppol_recu_at')
            ->take(10)
            ->get();

        return view('peppol.dashboard', compact(
            'params',
            'facturesTotal', 'facturesPeppol', 'facturesAEnvoyer', 'facturesSansTva',
            'avoirsTotal', 'avoirsPeppol',
            'achatsTotal', 'achatsPeppol', 'achatsOcr', 'achatsManuel',
            'webhookLogs', 'webhookErrors',
            'dernieresReceptions'
        ));
    }
}
