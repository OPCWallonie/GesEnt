<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
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

        $debutAnnee   = Carbon::create($annee, 1, 1)->startOfYear();
        $finAnnee     = Carbon::create($annee, 12, 31)->endOfYear();
        $debutAnneeN1 = Carbon::create($annee - 1, 1, 1)->startOfYear();
        $finAnneeN1   = Carbon::create($annee - 1, 12, 31)->endOfYear();

        // --- CA mensuel ventes vs achats ---
        $moisLabels  = [];
        $caVentes    = [];
        $caAchats    = [];
        $caMarges    = [];
        $caVentesN1  = [];

        for ($m = 1; $m <= 12; $m++) {
            $debut  = Carbon::create($annee, $m, 1)->startOfMonth();
            $fin    = $debut->copy()->endOfMonth();
            $debutN1 = Carbon::create($annee - 1, $m, 1)->startOfMonth();
            $finN1   = $debutN1->copy()->endOfMonth();

            $vente   = Facture::whereBetween('date_document', [$debut, $fin])
                ->whereIn('statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
                ->sum('montant_ttc');
            $achat   = FactureAchat::whereBetween('date_document', [$debut, $fin])->sum('montant_ttc');
            $venteN1 = Facture::whereBetween('date_document', [$debutN1, $finN1])
                ->whereIn('statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
                ->sum('montant_ttc');

            $moisLabels[] = $debut->locale('fr')->isoFormat('MMM');
            $caVentes[]   = round($vente, 2);
            $caAchats[]   = round($achat, 2);
            $caMarges[]   = round($vente - $achat, 2);
            $caVentesN1[] = round($venteN1, 2);
        }

        // --- KPIs année N ---
        $totalVentes = Facture::whereBetween('date_document', [$debutAnnee, $finAnnee])
            ->whereIn('statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
            ->sum('montant_ttc');
        $totalAchats = FactureAchat::whereBetween('date_document', [$debutAnnee, $finAnnee])->sum('montant_ttc');
        $totalEncaisse = Facture::whereBetween('date_paiement', [$debutAnnee, $finAnnee])
            ->where('statut', 'payee')->sum('montant_paye');

        // --- KPIs année N-1 (pour comparaison) ---
        $ventesN1   = Facture::whereBetween('date_document', [$debutAnneeN1, $finAnneeN1])
            ->whereIn('statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
            ->sum('montant_ttc');
        $achatsN1   = FactureAchat::whereBetween('date_document', [$debutAnneeN1, $finAnneeN1])->sum('montant_ttc');
        $encaisseN1 = Facture::whereBetween('date_paiement', [$debutAnneeN1, $finAnneeN1])
            ->where('statut', 'payee')->sum('montant_paye');
        $margeN1    = $ventesN1 - $achatsN1;

        // --- Taux de conversion N et N-1 ---
        $nbDevis = Devis::whereBetween('date_document', [$debutAnnee, $finAnnee])->count();
        $nbBdc   = BonCommande::whereBetween('date_document', [$debutAnnee, $finAnnee])->count();
        $tauxConversion = $nbDevis > 0 ? round(($nbBdc / $nbDevis) * 100, 1) : 0;

        // --- DSO ---
        $creancesClients = Facture::whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
            ->sum('montant_net_a_payer');
        $dso = $totalVentes > 0 ? round(($creancesClients / $totalVentes) * 365, 0) : 0;

        $dsoReel = Facture::whereBetween('date_document', [$debutAnnee, $finAnnee])
            ->where('statut', 'payee')
            ->whereNotNull('date_paiement')
            ->whereNotNull('date_document')
            ->selectRaw('AVG(DATEDIFF(date_paiement, date_document)) as dso_moyen')
            ->value('dso_moyen');
        $dsoReel = $dsoReel ? (int) round($dsoReel) : null;

        // --- Funnel de conversion ---
        $devisEmis          = Devis::whereBetween('date_document', [$debutAnnee, $finAnnee])->count();
        $devisAcceptes      = Devis::whereBetween('date_document', [$debutAnnee, $finAnnee])->where('statut', 'valide')->count();
        $bdcGeneres         = BonCommande::whereBetween('date_document', [$debutAnnee, $finAnnee])->count();
        $facturesEmises     = Facture::whereBetween('date_document', [$debutAnnee, $finAnnee])->count();
        $facturesPayees     = Facture::whereBetween('date_document', [$debutAnnee, $finAnnee])->where('statut', 'payee')->count();

        $montantDevis       = Devis::whereBetween('date_document', [$debutAnnee, $finAnnee])->sum('montant_ttc');
        $montantAcceptes    = Devis::whereBetween('date_document', [$debutAnnee, $finAnnee])->where('statut', 'valide')->sum('montant_ttc');
        $montantBdc         = BonCommande::whereBetween('date_document', [$debutAnnee, $finAnnee])->sum('montant_ttc');
        $montantFacture     = Facture::whereBetween('date_document', [$debutAnnee, $finAnnee])->sum('montant_ttc');
        $montantEncaisse    = Facture::whereBetween('date_document', [$debutAnnee, $finAnnee])->where('statut', 'payee')->sum('montant_paye');

        // --- Top 5 clients par CA ---
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

        // --- Achats par catégorie ---
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
            'moisLabels', 'caVentes', 'caAchats', 'caMarges', 'caVentesN1',
            'totalVentes', 'totalAchats', 'totalEncaisse',
            'ventesN1', 'achatsN1', 'encaisseN1', 'margeN1',
            'nbDevis', 'nbBdc', 'tauxConversion',
            'dso', 'dsoReel', 'creancesClients',
            'devisEmis', 'devisAcceptes', 'bdcGeneres', 'facturesEmises', 'facturesPayees',
            'montantDevis', 'montantAcceptes', 'montantBdc', 'montantFacture', 'montantEncaisse',
            'topClients', 'achatsParCategorie', 'topFournisseurs',
            'facturesEnRetard', 'totalEnRetard'
        ));
    }

    public function balanceAgee()
    {
        $factures = Facture::with('client')
            ->whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
            ->whereNotNull('date_echeance')
            ->orderBy('date_echeance')
            ->get();

        $joursRetard = fn($f) => (int) $f->date_echeance->diffInDays(now());

        $tranches = [
            'non_echues' => $factures->filter(fn($f) => $f->date_echeance->isFuture()),
            '0_30'       => $factures->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) <= 30),
            '31_60'      => $factures->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) > 30 && $joursRetard($f) <= 60),
            '61_90'      => $factures->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) > 60 && $joursRetard($f) <= 90),
            'plus_90'    => $factures->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) > 90),
        ];

        $parClient = $factures->groupBy('client_id')->map(function ($group) use ($joursRetard) {
            return [
                'client'     => $group->first()->client,
                'non_echues' => $group->filter(fn($f) => $f->date_echeance->isFuture())->sum('montant_net_a_payer'),
                '0_30'       => $group->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) <= 30)->sum('montant_net_a_payer'),
                '31_60'      => $group->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) > 30 && $joursRetard($f) <= 60)->sum('montant_net_a_payer'),
                '61_90'      => $group->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) > 60 && $joursRetard($f) <= 90)->sum('montant_net_a_payer'),
                'plus_90'    => $group->filter(fn($f) => $f->date_echeance->isPast() && $joursRetard($f) > 90)->sum('montant_net_a_payer'),
                'total'      => $group->sum('montant_net_a_payer'),
                'factures'   => $group,
            ];
        })->sortByDesc('total');

        return view('statistiques.balance-agee', compact('tranches', 'parClient'));
    }

    public function tresorerie()
    {
        $semaines = collect();

        for ($i = 0; $i < 12; $i++) {
            $debut = now()->addWeeks($i)->startOfWeek();
            $fin   = $debut->copy()->endOfWeek();

            $entrees = Facture::whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
                ->whereBetween('date_echeance', [$debut, $fin])
                ->sum('montant_net_a_payer');

            $sorties = FactureAchat::where('statut', 'en_attente')
                ->whereBetween('date_echeance', [$debut, $fin])
                ->sum('montant_ttc');

            $semaines->push([
                'label'   => 'S' . $debut->weekOfYear . ' (' . $debut->format('d/m') . ')',
                'debut'   => $debut->format('d/m/Y'),
                'fin'     => $fin->format('d/m/Y'),
                'entrees' => round($entrees, 2),
                'sorties' => round($sorties, 2),
                'solde'   => round($entrees - $sorties, 2),
            ]);
        }

        // Calcul du solde cumulé
        $cumulatif = 0;
        $semaines = $semaines->map(function ($s) use (&$cumulatif) {
            $cumulatif += $s['solde'];
            $s['solde_cumule'] = round($cumulatif, 2);
            return $s;
        });

        return view('statistiques.tresorerie', compact('semaines'));
    }

    public function chantiersRentabilite()
    {
        $chantiers = Chantier::where('statut', '!=', 'archive')
            ->with('client')
            ->get()
            ->map(function ($c) {
                $ventes = $c->totalVentes();
                $achats = $c->totalAchats();
                $marge  = $ventes - $achats;
                return [
                    'chantier'    => $c,
                    'ventes'      => $ventes,
                    'achats'      => $achats,
                    'marge'       => $marge,
                    'taux_marge'  => $ventes > 0 ? ($marge / $ventes) * 100 : null,
                    'avancement'  => $c->avancement ?? 0,
                    'nb_factures' => $c->factures()->count(),
                ];
            })
            ->filter(fn($c) => $c['ventes'] > 0 || $c['achats'] > 0)
            ->sortByDesc('marge')
            ->values();

        return view('statistiques.chantiers-rentabilite', compact('chantiers'));
    }
}
