<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Devis;
use App\Models\BonCommande;
use App\Models\Facture;
use App\Models\FactureAchat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatistiquesController extends Controller
{
    public function index()
    {
        $annee   = (int) request('annee', now()->year);
        $annees  = range(now()->year, max(now()->year - 4, 2024));

        // --- CA mensuel ventes vs achats (12 mois de l'année sélectionnée) ---
        $moisLabels = [];
        $caVentes   = [];
        $caAchats   = [];
        $caMarges   = [];

        for ($m = 1; $m <= 12; $m++) {
            $debut = Carbon::create($annee, $m, 1)->startOfMonth();
            $fin   = $debut->copy()->endOfMonth();

            $vente = Facture::whereBetween('date_document', [$debut, $fin])
                ->whereIn('statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
                ->sum('montant_ttc');

            $achat = FactureAchat::whereBetween('date_document', [$debut, $fin])
                ->sum('montant_ttc');

            $moisLabels[] = $debut->locale('fr')->isoFormat('MMM');
            $caVentes[]   = round($vente, 2);
            $caAchats[]   = round($achat, 2);
            $caMarges[]   = round($vente - $achat, 2);
        }

        // --- KPIs annuels ---
        $debutAnnee = Carbon::create($annee, 1, 1)->startOfYear();
        $finAnnee   = Carbon::create($annee, 12, 31)->endOfYear();

        $totalVentes = Facture::whereBetween('date_document', [$debutAnnee, $finAnnee])
            ->whereIn('statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
            ->sum('montant_ttc');

        $totalAchats = FactureAchat::whereBetween('date_document', [$debutAnnee, $finAnnee])
            ->sum('montant_ttc');

        $totalEncaisse = Facture::whereBetween('date_paiement', [$debutAnnee, $finAnnee])
            ->where('statut', 'payee')
            ->sum('montant_paye');

        $nbDevis = Devis::whereBetween('date_document', [$debutAnnee, $finAnnee])->count();
        $nbBdc   = BonCommande::whereBetween('date_document', [$debutAnnee, $finAnnee])->count();
        $tauxConversion = $nbDevis > 0 ? round(($nbBdc / $nbDevis) * 100, 1) : 0;

        // --- Top 5 clients par CA (ventes) ---
        $topClients = DB::table('factures')
            ->join('clients', 'factures.client_id', '=', 'clients.id')
            ->whereNull('factures.deleted_at')
            ->whereBetween('factures.date_document', [$debutAnnee, $finAnnee])
            ->whereIn('factures.statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
            ->groupBy('clients.id', 'clients.nom')
            ->select('clients.nom', DB::raw('SUM(factures.montant_ttc) as total'))
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // --- Répartition achats par catégorie ---
        $achatsParCategorie = FactureAchat::whereBetween('date_document', [$debutAnnee, $finAnnee])
            ->groupBy('categorie')
            ->select('categorie', DB::raw('SUM(montant_ttc) as total'))
            ->orderByDesc('total')
            ->get();

        // --- Top 5 fournisseurs ---
        $topFournisseurs = DB::table('factures_achat')
            ->join('fournisseurs', 'factures_achat.fournisseur_id', '=', 'fournisseurs.id')
            ->whereNull('factures_achat.deleted_at')
            ->whereBetween('factures_achat.date_document', [$debutAnnee, $finAnnee])
            ->groupBy('fournisseurs.id', 'fournisseurs.nom')
            ->select('fournisseurs.nom', DB::raw('SUM(factures_achat.montant_ttc) as total'))
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // --- Factures en retard ---
        $facturesEnRetard = Facture::with('client')
            ->whereIn('statut', ['en_attente', 'envoyee'])
            ->where('date_echeance', '<', now())
            ->orderBy('date_echeance')
            ->get();

        $totalEnRetard = $facturesEnRetard->sum('montant_net_a_payer');

        return view('statistiques.index', compact(
            'annee', 'annees',
            'moisLabels', 'caVentes', 'caAchats', 'caMarges',
            'totalVentes', 'totalAchats', 'totalEncaisse',
            'nbDevis', 'nbBdc', 'tauxConversion',
            'topClients', 'achatsParCategorie', 'topFournisseurs',
            'facturesEnRetard', 'totalEnRetard'
        ));
    }
}
