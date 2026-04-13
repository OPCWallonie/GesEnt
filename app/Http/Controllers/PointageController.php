<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Ouvrier;
use App\Models\Pointage;
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

        $ouvriers = Ouvrier::where('actif', true)
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

        $semainePrecedente = $lundi->copy()->subWeek()->format('Y-m-d');
        $semaineSuivante   = $lundi->copy()->addWeek()->format('Y-m-d');

        return view('pointages.index', compact(
            'lundi', 'jours', 'ouvriers', 'chantiers',
            'pointages', 'totaux', 'semainePrecedente', 'semaineSuivante'
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

        // Copie snapshot cout_horaire
        $data['cout_horaire'] = Ouvrier::find($data['ouvrier_id'])?->cout_horaire ?? 0;

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
            ? Carbon::parse($request->semaine)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $lundiPrec    = $lundi->copy()->subWeek();
        $vendrediPrec = $lundiPrec->copy()->addDays(4);

        $precedents = Pointage::whereBetween('date', [$lundiPrec, $vendrediPrec])->get();

        // Ouvriers actifs indexés par id (pour le cout_horaire à jour)
        $ouvrierActifs = Ouvrier::where('actif', true)->get()->keyBy('id');

        $copies = 0;
        foreach ($precedents as $p) {
            // Ignorer si l'ouvrier est désormais inactif/supprimé
            if (! isset($ouvrierActifs[$p->ouvrier_id])) {
                continue;
            }

            $offset      = (int) $p->date->diffInDays($lundiPrec);
            $nouvelleDate = $lundi->copy()->addDays($offset);

            // Ne pas écraser un créneau déjà saisi pour cet ouvrier ce jour-là
            $existe = Pointage::where('ouvrier_id', $p->ouvrier_id)
                ->where('date', $nouvelleDate)
                ->exists();

            if (! $existe) {
                Pointage::create([
                    'ouvrier_id'  => $p->ouvrier_id,
                    'chantier_id' => $p->chantier_id,
                    'date'        => $nouvelleDate,
                    'heures'      => $p->heures,
                    'heures_sup'  => $p->heures_sup,
                    'cout_horaire' => $ouvrierActifs[$p->ouvrier_id]->cout_horaire,
                    'notes'       => $p->notes,
                ]);
                $copies++;
            }
        }

        $msg = $copies > 0
            ? "{$copies} pointage(s) recopiés depuis la semaine du {$lundiPrec->format('d/m/Y')}. Vérifiez et ajustez si nécessaire."
            : "Semaine du {$lundiPrec->format('d/m/Y')} vide, rien à recopier.";

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
