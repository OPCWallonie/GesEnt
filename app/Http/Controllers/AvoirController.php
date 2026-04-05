<?php

namespace App\Http\Controllers;

use App\Models\Avoir;
use App\Models\Facture;
use App\Models\ParametresEntreprise;
use App\Services\NumerotationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AvoirController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function create(Facture $facture)
    {
        $facture->load('client', 'chantier');
        return view('avoirs.create', compact('facture'));
    }

    public function store(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'date_document' => 'required|date',
            'motif'         => 'required|string|max:500',
            'montant_ht'    => 'required|numeric|min:0.01',
            'taux_tva'      => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $tva = $data['montant_ht'] * ($data['taux_tva'] / 100);

        $avoir = Avoir::create([
            'numero'        => $this->numerotation->suivant('avoir'),
            'facture_id'    => $facture->id,
            'client_id'     => $facture->client_id,
            'chantier_id'   => $facture->chantier_id,
            'created_by'    => auth()->id(),
            'date_document' => $data['date_document'],
            'motif'         => $data['motif'],
            'montant_ht'    => $data['montant_ht'],
            'taux_tva'      => $data['taux_tva'],
            'montant_tva'   => $tva,
            'montant_ttc'   => $data['montant_ht'] + $tva,
            'notes'         => $data['notes'] ?? null,
        ]);

        return redirect()->route('avoirs.show', $avoir)
            ->with('success', "Avoir {$avoir->numero} créé.");
    }

    public function show(Avoir $avoir)
    {
        $avoir->load('facture', 'client', 'chantier');
        $parametres = ParametresEntreprise::instance();
        return view('avoirs.show', compact('avoir', 'parametres'));
    }

    public function destroy(Avoir $avoir)
    {
        $avoir->delete();
        return redirect()->route('factures.show', $avoir->facture_id)
            ->with('success', "Avoir {$avoir->numero} supprimé.");
    }

    public function pdf(Avoir $avoir)
    {
        $avoir->load('facture', 'client', 'chantier');
        $parametres = ParametresEntreprise::instance();

        $pdf = Pdf::loadView('pdf.avoir', compact('avoir', 'parametres'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("avoir-{$avoir->numero}.pdf");
    }
}
