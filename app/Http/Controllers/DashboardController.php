<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Certification;
use App\Models\Chantier;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\Ouvrier;
use App\Models\Pointage;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $maintenant = Carbon::now();
        $debutMois  = $maintenant->copy()->startOfMonth();
        $finMois    = $maintenant->copy()->endOfMonth();

        $caMois = Facture::whereBetween('date_document', [$debutMois, $finMois])
            ->whereIn('statut', ['payee', 'en_attente', 'envoyee', 'en_retard'])
            ->sum('montant_ttc');

        $achatsMois = FactureAchat::whereBetween('date_document', [$debutMois, $finMois])
            ->sum('montant_ttc');

        $stats = [
            'devis_en_attente'    => Devis::whereIn('statut', ['en_attente', 'valide'])->count(),
            'bdc_en_cours'        => BonCommande::whereIn('statut', ['valide', 'en_cours'])->count(),
            'factures_en_attente' => Facture::whereIn('statut', ['en_attente', 'envoyee'])->count(),
            'ca_mois'             => $caMois,
            'achats_mois'         => $achatsMois,
            'marge_mois'          => $caMois - $achatsMois,
            'a_encaisser'         => Facture::whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
                                        ->sum('montant_net_a_payer'),
            'a_payer_fournisseurs' => FactureAchat::where('statut', 'en_attente')->sum('montant_ttc'),
        ];

        $facturesEnRetard = Facture::with('client')
            ->whereIn('statut', ['en_attente', 'envoyee'])
            ->where('date_echeance', '<', $maintenant)
            ->orderBy('date_echeance')
            ->take(5)
            ->get();

        $derniersDevis = Devis::with('client')
            ->orderByDesc('date_document')
            ->take(5)
            ->get();

        $dernieresFactures = Facture::with('client')
            ->orderByDesc('date_document')
            ->take(5)
            ->get();

        $devisExpirantBientot = Devis::with('client')
            ->whereIn('statut', ['en_attente', 'valide'])
            ->whereBetween('date_validite', [$maintenant, $maintenant->copy()->addDays(7)])
            ->orderBy('date_validite')
            ->get();

        // Factures fournisseurs en retard
        $achatsEnRetard = FactureAchat::with('fournisseur')
            ->where('statut', 'en_attente')
            ->where('date_echeance', '<', $maintenant)
            ->orderBy('date_echeance')
            ->take(5)
            ->get();

        // Chantiers actifs avec avancement
        $chantiersActifs = Chantier::with('client')
            ->whereIn('statut', ['actif'])
            ->orderBy('avancement')
            ->take(6)
            ->get();

        // MO : stats semaine courante
        $lundi     = $maintenant->copy()->startOfWeek();
        $vendredi  = $lundi->copy()->addDays(4);
        $moSemaine = Pointage::whereBetween('date', [$lundi, $vendredi])->sum('cout_total');
        // Dénominateur : ouvriers actifs disponibles cette semaine
        // (exclut ceux absents sur toute la semaine lun-ven)
        $nbOuvriersActifs = Ouvrier::where('actif', true)
            ->whereDoesntHave('absences', fn($q) => $q
                ->where('date_debut', '<=', $lundi->toDateString())
                ->where('date_fin', '>=', $vendredi->toDateString())
            )
            ->count();
        $nbOuvriersPlanifies  = Pointage::whereBetween('date', [$lundi, $vendredi])
            ->distinct('ouvrier_id')->count('ouvrier_id');

        // Certifications expirant dans les 90 jours (ouvriers actifs uniquement)
        $certificationsARenouveler = Certification::with('ouvrier')
            ->whereHas('ouvrier', fn($q) => $q->where('actif', true))
            ->whereNotNull('date_expiration')
            ->where('date_expiration', '<=', $maintenant->copy()->addDays(90))
            ->orderBy('date_expiration')
            ->get();

        return view('dashboard.index', compact(
            'stats', 'facturesEnRetard', 'derniersDevis',
            'dernieresFactures', 'devisExpirantBientot', 'achatsEnRetard',
            'chantiersActifs', 'moSemaine', 'nbOuvriersActifs', 'nbOuvriersPlanifies',
            'certificationsARenouveler'
        ));
    }
}
