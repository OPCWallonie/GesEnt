<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Pointage;
use App\Models\ReposCollectif;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PointageController extends Controller
{
    /**
     * Grille de planning hebdomadaire (rows=ouvriers, cols=lun-ven)
     */
    public function index(Request $request)
    {
        $lundi = $request->filled('semaine')
            ? Carbon::parse($request->semaine)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $jours = collect(range(0, 4))->map(fn($i) => $lundi->copy()->addDays($i));

        $vendredi = $lundi->copy()->addDays(4);

        // Personnel planifiable (ouvriers + employés terrain), actifs,
        // sauf ceux absents sur TOUTE la semaine affichée
        $ouvriers = Ouvrier::planifiable()
            ->where('actif', true)
            ->whereDoesntHave('absences', fn($q) => $q
                ->where('date_debut', '<=', $lundi->toDateString())
                ->where('date_fin', '>=', $vendredi->toDateString())
            )
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        $chantiers = Chantier::whereNotIn('statut', ['archive'])
            ->orderBy('nom')
            ->get(['id', 'nom']);

        // Pointages de la semaine indexés par [ouvrier_id][date]
        $pointages = Pointage::with('chantier')
            ->whereBetween('date', [$lundi, $lundi->copy()->addDays(4)])
            ->get()
            ->groupBy('ouvrier_id')
            ->map(fn($rows) => $rows->keyBy(fn($p) => $p->date->format('Y-m-d')));

        // Total heures semaine par ouvrier
        $totaux = $pointages->map(fn($jrs) => $jrs->sum(fn($p) => $p->heures + $p->heures_sup));

        // Absences chevauchant la semaine, indexées par [ouvrier_id]
        $absences = Absence::where('date_debut', '<=', $vendredi->toDateString())
            ->where('date_fin', '>=', $lundi->toDateString())
            ->get()
            ->groupBy('ouvrier_id');

        $semainePrecedente = $lundi->copy()->subWeek()->format('Y-m-d');
        $semaineSuivante   = $lundi->copy()->addWeek()->format('Y-m-d');

        // RC collectifs non encore appliqués prévus dans la semaine affichée
        $reposCollectifsEnAttente = ReposCollectif::where('applique', false)
            ->whereBetween('date', [$lundi->toDateString(), $vendredi->toDateString()])
            ->orderBy('date')
            ->get();

        return view('pointages.index', compact(
            'lundi', 'jours', 'ouvriers', 'chantiers',
            'pointages', 'totaux', 'absences',
            'semainePrecedente', 'semaineSuivante',
            'reposCollectifsEnAttente'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ouvrier_id'  => 'required|exists:ouvriers,id',
            'chantier_id' => 'required|exists:chantiers,id',
            'date'        => 'required|date',
            'heures'      => 'required|numeric|min:0|max:24',
            'heures_sup'  => 'nullable|numeric|min:0|max:12',
            'notes'       => 'nullable|string|max:500',
        ]);

        $data['heures_sup'] ??= 0;

        // Snapshot du coût horaire effectif (direct ou converti depuis le mensuel)
        $data['cout_horaire'] = Ouvrier::find($data['ouvrier_id'])?->cout_horaire_effectif ?? 0;

        Pointage::updateOrCreate(
            ['ouvrier_id' => $data['ouvrier_id'], 'date' => $data['date'], 'chantier_id' => $data['chantier_id']],
            $data
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Pointage enregistré.');
    }

    /**
     * Recopie les pointages de la semaine précédente vers la semaine en cours.
     * N'écrase pas les créneaux déjà saisis.
     */
    public function copier(Request $request)
    {
        $lundi = $request->filled('semaine')
            ? Carbon::parse($request->semaine)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $lundiPrec    = $lundi->copy()->subWeek();
        $vendrediPrec = $lundiPrec->copy()->addDays(4);

        $precedents = Pointage::whereBetween('date', [
            $lundiPrec->toDateString(),
            $vendrediPrec->toDateString(),
        ])->get();

        // Personnel planifiable actif indexé par id (pour le cout_horaire_effectif à jour)
        $ouvrierActifs = Ouvrier::planifiable()->where('actif', true)->get()->keyBy('id');

        $copies = 0;
        foreach ($precedents as $p) {
            // Ignorer si l'ouvrier est désormais inactif/supprimé
            if (! isset($ouvrierActifs[$p->ouvrier_id])) {
                continue;
            }

            // Offset basé sur le jour ISO (1=lun … 5=ven), indépendant des timezones
            $offsetJour   = $p->date->dayOfWeekIso - 1;  // 0=lun, 1=mar, …, 4=ven
            $nouvelleDate = $lundi->copy()->addDays($offsetJour)->toDateString();

            // Ne pas écraser un créneau déjà saisi pour cet ouvrier ce jour-là
            $existe = Pointage::where('ouvrier_id', $p->ouvrier_id)
                ->whereDate('date', $nouvelleDate)
                ->exists();

            if (! $existe) {
                Pointage::create([
                    'ouvrier_id'   => $p->ouvrier_id,
                    'chantier_id'  => $p->chantier_id,
                    'date'         => $nouvelleDate,
                    'heures'       => $p->heures,
                    'heures_sup'   => $p->heures_sup,
                    'cout_horaire' => $ouvrierActifs[$p->ouvrier_id]->cout_horaire_effectif,
                    'notes'        => $p->notes,
                ]);
                $copies++;
            }
        }

        $msg = $copies > 0
            ? "{$copies} pointage(s) recopiés depuis la semaine du {$lundiPrec->format('d/m/Y')}. Vérifiez et ajustez si nécessaire."
            : "Semaine du {$lundiPrec->format('d/m/Y')} vide (ou déjà complète), rien à recopier.";

        return redirect()
            ->route('pointages.index', ['semaine' => $lundi->format('Y-m-d')])
            ->with('success', $msg);
    }

    public function destroy(Pointage $pointage)
    {
        $pointage->delete();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Pointage supprimé.');
    }

    /**
     * Vue pointages regroupés par chantier
     */
    public function parChantier(Request $request)
    {
        $annee = (int) $request->get('annee', now()->year);
        $mois  = $request->filled('mois') ? (int) $request->mois : null;

        $query = Pointage::with(['ouvrier', 'chantier.client'])
            ->whereYear('date', $annee);

        if ($mois) {
            $query->whereMonth('date', $mois);
        }

        $pointages = $query->orderBy('date')->get();

        // Regroupement chantier → ouvrier → pointages
        $parChantier = $pointages
            ->groupBy('chantier_id')
            ->map(fn($rows) => [
                'chantier'  => $rows->first()->chantier,
                'ouvriers'  => $rows->groupBy('ouvrier_id')->map(fn($r) => [
                    'ouvrier'      => $r->first()->ouvrier,
                    'heures'       => $r->sum('heures'),
                    'heures_sup'   => $r->sum('heures_sup'),
                    'cout_total'   => $r->sum('cout_total'),
                    'nb_pointages' => $r->count(),
                ])->values(),
                'cout_total' => $rows->sum('cout_total'),
                'heures'     => $rows->sum('heures'),
                'heures_sup' => $rows->sum('heures_sup'),
            ])
            ->sortByDesc('cout_total')
            ->values();

        $totalCout   = $parChantier->sum('cout_total');
        $totalHeures = $parChantier->sum('heures');

        return view('pointages.par-chantier', compact('parChantier', 'annee', 'mois', 'totalCout', 'totalHeures'));
    }
}
