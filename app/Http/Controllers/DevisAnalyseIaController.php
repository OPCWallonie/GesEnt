<?php

namespace App\Http\Controllers;

use App\Models\Devis;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\AnalyseIaDevisService;
use Illuminate\Support\Facades\Log;

class DevisAnalyseIaController extends Controller
{
    public function __construct(private AnalyseIaDevisService $service) {}

    public function analyser(Devis $devis)
    {
        if (! ParametresEntreprise::instance()->aiConfiguree()) {
            return back()->with('error', 'Configurez un moteur IA dans Paramètres > Intégrations.');
        }

        try {
            $analyse = $this->service->analyser($devis);

            if ($analyse === null) {
                return back()->with('info', 'Aucun produit à enjeu détecté dans ce devis.');
            }

            return back()->with('success', 'Analyse IA générée.');
        } catch (\Throwable $e) {
            Log::error('Erreur analyse IA devis', [
                'devis_id' => $devis->id,
                'message'  => $e->getMessage(),
            ]);
            return back()->with('error', 'Impossible de générer l\'analyse : ' . $e->getMessage());
        }
    }

    public function invalider(Devis $devis)
    {
        $this->service->invalider($devis);
        return back()->with('success', 'Cache de l\'analyse IA invalidé.');
    }
}
