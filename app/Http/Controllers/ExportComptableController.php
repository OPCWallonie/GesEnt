<?php

namespace App\Http\Controllers;

use App\Services\ExportComptableService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportComptableController extends Controller
{
    public function index()
    {
        $annees = range(now()->year, max(now()->year - 4, 2020));
        return view('export-comptable.index', compact('annees'));
    }

    public function export(Request $request, ExportComptableService $service): StreamedResponse
    {
        $data = $request->validate([
            'type'   => 'required|in:ventes,achats',
            'annee'  => 'required|integer|min:2020|max:2030',
            'mois'   => 'nullable|integer|min:1|max:12',
            'format' => 'required|in:winbooks,exact,bob,horus',
        ]);

        $annee  = (int)$data['annee'];
        $mois   = $data['mois'] ? (int)$data['mois'] : null;
        $format = $data['format'];
        $type   = $data['type'];

        $csv = $type === 'ventes'
            ? $service->exportVentes($annee, $mois, $format)
            : $service->exportAchats($annee, $mois, $format);

        $periode = $mois ? sprintf('%04d-%02d', $annee, $mois) : $annee;
        $filename = "gesent_{$type}_{$format}_{$periode}.csv";

        return response()->streamDownload(function () use ($csv) {
            // BOM UTF-8 pour Excel
            echo "\xEF\xBB\xBF" . $csv;
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
