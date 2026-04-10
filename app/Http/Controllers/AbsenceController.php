<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Ouvrier;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $annee = (int) $request->get('annee', now()->year);

        $query = Absence::with('ouvrier')
            ->whereYear('date_debut', $annee)
            ->orderByDesc('date_debut');

        if ($request->filled('ouvrier_id')) {
            $query->where('ouvrier_id', $request->ouvrier_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $absences = $query->paginate(30)->withQueryString();

        $ouvriers = Ouvrier::where('actif', true)->orderBy('nom')->get(['id', 'nom', 'prenom']);

        return view('absences.index', compact('absences', 'ouvriers', 'annee'));
    }

    public function create(Request $request)
    {
        $absence  = new Absence(['date_debut' => today(), 'date_fin' => today(), 'justifie' => true]);
        $ouvriers = Ouvrier::where('actif', true)->orderBy('nom')->get(['id', 'nom', 'prenom']);
        $ouvrierId = $request->ouvrier_id;
        return view('absences.edit', compact('absence', 'ouvriers', 'ouvrierId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ouvrier_id' => 'required|exists:ouvriers,id',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'type'       => 'required|in:' . implode(',', array_keys(Absence::TYPES)),
            'justifie'   => 'boolean',
            'motif'      => 'nullable|string|max:500',
        ]);

        $data['justifie'] = $request->boolean('justifie', true);
        Absence::create($data);

        return redirect()->route('absences.index')
            ->with('success', 'Absence enregistrée.');
    }

    public function edit(Absence $absence)
    {
        $ouvriers = Ouvrier::where('actif', true)->orderBy('nom')->get(['id', 'nom', 'prenom']);
        return view('absences.edit', compact('absence', 'ouvriers'));
    }

    public function update(Request $request, Absence $absence)
    {
        $data = $request->validate([
            'ouvrier_id' => 'required|exists:ouvriers,id',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'type'       => 'required|in:' . implode(',', array_keys(Absence::TYPES)),
            'justifie'   => 'boolean',
            'motif'      => 'nullable|string|max:500',
        ]);

        $data['justifie'] = $request->boolean('justifie', true);
        $absence->update($data);

        return redirect()->route('absences.index')
            ->with('success', 'Absence mise à jour.');
    }

    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect()->route('absences.index')
            ->with('success', 'Absence supprimée.');
    }
}
