<?php

namespace App\Http\Controllers;

use App\Models\ChargeFonctionnement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChargeFonctionnementController extends Controller
{
    public function index()
    {
        $charges = ChargeFonctionnement::orderBy('categorie')->orderBy('libelle')->get();

        // Total mensuel normalisé de toutes les charges actives
        $totalMensuel = $charges
            ->where('actif', true)
            ->sum('montant_mensuel_normalise');

        // Groupement par catégorie pour l'affichage
        $parCategorie = $charges->groupBy('categorie');

        return view('charges-fonctionnement.index', compact('charges', 'parCategorie', 'totalMensuel'));
    }

    public function create()
    {
        $charge = new ChargeFonctionnement(['date_debut' => today(), 'actif' => true]);
        return view('charges-fonctionnement.edit', compact('charge'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCharge($request);
        $data['actif'] = $request->boolean('actif', true);

        ChargeFonctionnement::create($data);

        return redirect()->route('charges-fonctionnement.index')
            ->with('success', 'Charge enregistrée.');
    }

    public function edit(ChargeFonctionnement $chargesFonctionnement)
    {
        $charge = $chargesFonctionnement;
        return view('charges-fonctionnement.edit', compact('charge'));
    }

    public function update(Request $request, ChargeFonctionnement $chargesFonctionnement)
    {
        $data = $this->validateCharge($request);
        $data['actif'] = $request->boolean('actif', true);

        $chargesFonctionnement->update($data);

        return redirect()->route('charges-fonctionnement.index')
            ->with('success', 'Charge mise à jour.');
    }

    public function destroy(ChargeFonctionnement $chargesFonctionnement)
    {
        $chargesFonctionnement->delete();

        return redirect()->route('charges-fonctionnement.index')
            ->with('success', 'Charge supprimée.');
    }

    private function validateCharge(Request $request): array
    {
        return $request->validate([
            'libelle'         => 'required|string|max:150',
            'categorie'       => ['required', Rule::in(array_keys(ChargeFonctionnement::CATEGORIES))],
            'montant_mensuel' => 'required|numeric|min:0',
            'periodicite'     => ['required', Rule::in(array_keys(ChargeFonctionnement::PERIODICITES))],
            'date_debut'      => 'required|date',
            'date_fin'        => 'nullable|date|after_or_equal:date_debut',
            'notes'           => 'nullable|string',
        ]);
    }
}
