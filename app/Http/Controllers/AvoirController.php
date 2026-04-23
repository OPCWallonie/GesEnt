<?php

namespace App\Http\Controllers;

use App\Models\Avoir;
use App\Models\Facture;
use App\Models\ParametresEntreprise;
use App\Services\NumerotationService;
use App\Services\OdooSyncService;
use App\Services\PeppolService;
use App\States\Avoir\Emis as AvoirEmis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AvoirController extends Controller
{
    public function __construct(
        private NumerotationService $numerotation,
        private OdooSyncService $odooSync,
    ) {}

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
            ->with('success', "Brouillon d'avoir créé. Cliquez sur \"Valider et émettre\" pour allouer un numéro officiel.");
    }

    public function emettre(Avoir $avoir)
    {
        if (!$avoir->estBrouillon()) {
            return back()->with('error', "Cet avoir a déjà été émis.");
        }

        try {
            DB::transaction(function () use ($avoir) {
                $numero = $this->numerotation->suivant('avoir');
                $avoir->update(['numero' => $numero]);
                $avoir->statut->transitionTo(AvoirEmis::class);
            });

            if (ParametresEntreprise::instance()->odooActif()) {
                $this->odooSync->syncAvoir($avoir->refresh());
            }

            return redirect()->route('avoirs.show', $avoir)
                ->with('success', "Avoir émis sous le numéro {$avoir->numero}.");
        } catch (\Throwable $e) {
            Log::error('Erreur émission avoir', [
                'avoir_id' => $avoir->id,
                'message'  => $e->getMessage(),
            ]);
            return back()->with('error', "Impossible d'émettre l'avoir : " . $e->getMessage());
        }
    }

    public function show(Avoir $avoir)
    {
        $avoir->load('facture', 'client', 'chantier');
        $parametres = ParametresEntreprise::instance();
        return view('avoirs.show', compact('avoir', 'parametres'));
    }

    public function destroy(Avoir $avoir)
    {
        if (!$avoir->estBrouillon()) {
            return back()->with('error', "Seuls les brouillons peuvent être supprimés.");
        }

        $factureId = $avoir->facture_id;
        $avoir->delete();
        return redirect()->route('factures.show', $factureId)
            ->with('success', "Brouillon d'avoir supprimé.");
    }

    public function envoyerPeppol(Avoir $avoir, PeppolService $peppol)
    {
        $resultat = $peppol->envoyerAvoir($avoir);

        if ($resultat['success']) {
            $avoir->update([
                'peppol_reference' => $resultat['reference'],
                'peppol_envoye_at' => now(),
            ]);
            return back()->with('success', $resultat['message']);
        }

        return back()->with('error', $resultat['message']);
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
