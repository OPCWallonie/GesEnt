<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\Devis;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function factures(Request $request): StreamedResponse
    {
        $query = Facture::with('client', 'chantier')
            ->when($request->annee, fn($q, $a) => $q->whereYear('date_document', $a))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->orderBy('date_document');

        $filename = 'factures_' . ($request->annee ?? now()->year) . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel

            fputcsv($handle, [
                'Numéro', 'Date', 'Échéance', 'Client', 'Chantier',
                'Statut', 'HT', 'TVA', 'TTC', 'Net à payer',
                'Date paiement', 'Montant encaissé', 'Nb relances',
            ], ';');

            $query->chunk(200, function ($factures) use ($handle) {
                foreach ($factures as $f) {
                    fputcsv($handle, [
                        $f->numero,
                        $f->date_document->format('d/m/Y'),
                        $f->date_echeance?->format('d/m/Y') ?? '',
                        $f->client->nom,
                        $f->chantier?->nom ?? '',
                        $f->statut,
                        number_format($f->montant_ht, 2, ',', ''),
                        number_format($f->montant_tva, 2, ',', ''),
                        number_format($f->montant_ttc, 2, ',', ''),
                        number_format($f->montant_net_a_payer, 2, ',', ''),
                        $f->date_paiement?->format('d/m/Y') ?? '',
                        number_format($f->montant_paye, 2, ',', ''),
                        $f->nb_relances,
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function facturesAchat(Request $request): StreamedResponse
    {
        $query = FactureAchat::with('fournisseur', 'chantier')
            ->when($request->annee, fn($q, $a) => $q->whereYear('date_document', $a))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->orderBy('date_document');

        $filename = 'achats_' . ($request->annee ?? now()->year) . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Numéro', 'Réf. fournisseur', 'Date', 'Échéance',
                'Fournisseur', 'Chantier', 'Catégorie',
                'Statut', 'HT', 'TVA %', 'TVA', 'TTC',
                'Date paiement',
            ], ';');

            $query->chunk(200, function ($factures) use ($handle) {
                foreach ($factures as $f) {
                    fputcsv($handle, [
                        $f->numero,
                        $f->reference_fournisseur ?? '',
                        $f->date_document->format('d/m/Y'),
                        $f->date_echeance?->format('d/m/Y') ?? '',
                        $f->fournisseur->nom,
                        $f->chantier?->nom ?? '',
                        $f->label_categorie,
                        $f->statut,
                        number_format($f->montant_ht, 2, ',', ''),
                        number_format($f->taux_tva, 2, ',', ''),
                        number_format($f->montant_tva, 2, ',', ''),
                        number_format($f->montant_ttc, 2, ',', ''),
                        $f->date_paiement?->format('d/m/Y') ?? '',
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function devis(Request $request): StreamedResponse
    {
        $query = Devis::with('client', 'chantier')
            ->when($request->annee, fn($q, $a) => $q->whereYear('date_document', $a))
            ->orderBy('date_document');

        $filename = 'devis_' . ($request->annee ?? now()->year) . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Numéro', 'Date', 'Validité', 'Client', 'Chantier', 'Statut', 'HT', 'TTC',
            ], ';');

            $query->chunk(200, function ($devis) use ($handle) {
                foreach ($devis as $d) {
                    fputcsv($handle, [
                        $d->numero,
                        $d->date_document->format('d/m/Y'),
                        $d->date_validite?->format('d/m/Y') ?? '',
                        $d->client->nom,
                        $d->chantier?->nom ?? '',
                        $d->statut,
                        number_format($d->montant_ht, 2, ',', ''),
                        number_format($d->montant_ttc, 2, ',', ''),
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
