<?php

namespace App\Http\Controllers;

use App\Models\Ouvrier;
use Illuminate\Http\Request;

class OuvrierController extends Controller
{
    public function index(Request $request)
    {
        $query = Ouvrier::withCount(['pointages'])
            ->withTrashed(false);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($s) => $s->where('nom', 'like', "%$q%")->orWhere('prenom', 'like', "%$q%"));
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if ($request->boolean('inactifs')) {
            $query->where('actif', false);
        } else {
            $query->where('actif', true);
        }

        $ouvriers = $query->orderBy('nom')->orderBy('prenom')->paginate(25)->withQueryString();

        return view('ouvriers.index', compact('ouvriers'));
    }

    public function create()
    {
        $ouvrier = new Ouvrier(['date_entree' => today()]);
        return view('ouvriers.edit', compact('ouvrier'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'             => 'required|string|max:100',
            'prenom'          => 'required|string|max:100',
            'numero_national' => 'nullable|string|max:20|unique:ouvriers,numero_national',
            'categorie'       => 'required|in:I,II,III,IV',
            'cout_horaire'    => 'required|numeric|min:0',
            'date_entree'     => 'required|date',
            'date_sortie'     => 'nullable|date|after_or_equal:date_entree',
            'telephone'       => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:100',
            'notes'           => 'nullable|string',
        ]);

        $data['actif'] = $request->boolean('actif', true);
        $ouvrier = Ouvrier::create($data);

        return redirect()->route('ouvriers.show', $ouvrier)
            ->with('success', "Ouvrier {$ouvrier->nom_complet} créé.");
    }

    public function show(Ouvrier $ouvrier)
    {
        $ouvrier->load(['pointages.chantier', 'absences']);

        // Semaine en cours pour le résumé
        $lundi    = now()->startOfWeek();
        $heureSem = $ouvrier->pointages()
            ->whereBetween('date', [$lundi, $lundi->copy()->addDays(6)])
            ->selectRaw('SUM(heures + heures_sup) as total')
            ->value('total') ?? 0;

        // Coût YTD
        $coutAnnee = $ouvrier->coutTotal(now()->year);

        // Derniers pointages
        $derniersPointages = $ouvrier->pointages()
            ->with('chantier')
            ->orderByDesc('date')
            ->limit(20)
            ->get();

        // Absences en cours / à venir
        $absencesActives = $ouvrier->absences()
            ->where('date_fin', '>=', today())
            ->orderBy('date_debut')
            ->get();

        $reposRestants = $ouvrier->reposCompensatoiresRestants(now()->year);

        return view('ouvriers.show', compact(
            'ouvrier', 'heureSem', 'coutAnnee',
            'derniersPointages', 'absencesActives', 'reposRestants'
        ));
    }

    public function edit(Ouvrier $ouvrier)
    {
        return view('ouvriers.edit', compact('ouvrier'));
    }

    public function update(Request $request, Ouvrier $ouvrier)
    {
        $data = $request->validate([
            'nom'             => 'required|string|max:100',
            'prenom'          => 'required|string|max:100',
            'numero_national' => "nullable|string|max:20|unique:ouvriers,numero_national,{$ouvrier->id}",
            'categorie'       => 'required|in:I,II,III,IV',
            'cout_horaire'    => 'required|numeric|min:0',
            'date_entree'     => 'required|date',
            'date_sortie'     => 'nullable|date|after_or_equal:date_entree',
            'telephone'       => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:100',
            'notes'           => 'nullable|string',
        ]);

        $data['actif'] = $request->boolean('actif', true);
        $ouvrier->update($data);

        return redirect()->route('ouvriers.show', $ouvrier)
            ->with('success', 'Ouvrier mis à jour.');
    }

    public function destroy(Ouvrier $ouvrier)
    {
        $ouvrier->delete();
        return redirect()->route('ouvriers.index')
            ->with('success', 'Ouvrier archivé.');
    }

    public function apiSearch(Request $request)
    {
        $q       = $request->get('q', '');
        $actif   = $request->boolean('actif', true);
        $results = Ouvrier::where('actif', $actif)
            ->where(fn($s) => $s->where('nom', 'like', "%$q%")->orWhere('prenom', 'like', "%$q%"))
            ->orderBy('nom')->orderBy('prenom')
            ->limit(20)
            ->get(['id', 'nom', 'prenom', 'categorie', 'cout_horaire'])
            ->map(fn($o) => [
                'id'           => $o->id,
                'nom_complet'  => $o->nom_complet,
                'categorie'    => $o->categorie,
                'cout_horaire' => $o->cout_horaire,
            ]);

        return response()->json($results);
    }
}
