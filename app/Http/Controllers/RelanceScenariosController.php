<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\RelanceEtape;
use App\Models\RelanceScenario;
use App\Services\MailTemplateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RelanceScenariosController extends Controller
{
    public function index()
    {
        $scenarios = RelanceScenario::withCount('factures')->with('etapes')->orderByDesc('est_defaut')->orderBy('nom')->get();
        return view('relance-scenarios.index', compact('scenarios'));
    }

    public function create()
    {
        $scenario = new RelanceScenario();
        return view('relance-scenarios.edit', compact('scenario'));
    }

    public function store(Request $request)
    {
        $data = $this->valider($request);

        $scenario = RelanceScenario::create([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
        ]);

        $this->sauvegarderEtapes($scenario, $data['etapes'] ?? []);

        return redirect()->route('relance-scenarios.index')
            ->with('success', "Scénario « {$scenario->nom} » créé.");
    }

    public function edit(RelanceScenario $relanceScenario)
    {
        $relanceScenario->load('etapes');
        return view('relance-scenarios.edit', ['scenario' => $relanceScenario]);
    }

    public function update(Request $request, RelanceScenario $relanceScenario)
    {
        $data = $this->valider($request);

        $relanceScenario->update([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
        ]);

        $relanceScenario->etapes()->delete();
        $this->sauvegarderEtapes($relanceScenario, $data['etapes'] ?? []);

        return redirect()->route('relance-scenarios.index')
            ->with('success', "Scénario « {$relanceScenario->nom} » mis à jour.");
    }

    public function destroy(RelanceScenario $relanceScenario)
    {
        if ($relanceScenario->est_defaut) {
            return back()->with('error', 'Le scénario par défaut ne peut pas être supprimé.');
        }

        $nom = $relanceScenario->nom;
        $relanceScenario->delete();

        return redirect()->route('relance-scenarios.index')
            ->with('success', "Scénario « {$nom} » supprimé.");
    }

    public function definirDefaut(RelanceScenario $relanceScenario)
    {
        $relanceScenario->definirDefaut();
        return back()->with('success', "« {$relanceScenario->nom} » est maintenant le scénario par défaut.");
    }

    public function apercu(RelanceScenario $relanceScenario, RelanceEtape $etape, Request $request)
    {
        // Générer un aperçu PDF du courrier de relance avec des données factices
        $facture    = new Facture([
            'numero'             => 'FAC-2026-0042',
            'montant_net_a_payer' => 1250.00,
            'montant_ttc'        => 1250.00,
            'date_echeance'      => now()->subDays((int) $request->input('jours', 21)),
            'nb_relances'        => $etape->numero_ordre - 1,
            'delai_reglement'    => 30,
        ]);
        // Simuler les relations sans base
        $facture->setRelation('client', (object) ['nom' => 'Client Exemple SRL', 'adresse' => 'Rue de la Paix 1', 'code_postal' => '1000', 'ville' => 'Bruxelles', 'numero_tva' => 'BE0123456789', 'statut_juridique' => 'SRL']);
        $facture->setRelation('chantier', null);

        $parametres = \App\Models\ParametresEntreprise::instance();

        $pdf = Pdf::loadView('pdf.courrier-relance', compact('facture', 'parametres', 'etape'))
            ->setPaper('a4', 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="apercu-relance.pdf"');
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'nom'                          => 'required|string|max:255',
            'description'                  => 'nullable|string',
            'etapes'                       => 'required|array|min:1',
            'etapes.*.numero_ordre'        => 'required|integer|min:1',
            'etapes.*.delai_jours'         => 'required|integer|min:1',
            'etapes.*.sujet'               => 'required|string|max:255',
            'etapes.*.corps_email'         => 'required|string',
            'etapes.*.canal'               => 'required|in:mail,courrier,les_deux',
            'etapes.*.ton'                 => 'required|in:cordial,ferme,formel',
            'etapes.*.actif'               => 'sometimes|boolean',
        ]);
    }

    private function sauvegarderEtapes(RelanceScenario $scenario, array $etapes): void
    {
        // Trier par delai_jours et renuméroter
        usort($etapes, fn($a, $b) => (int) $a['delai_jours'] <=> (int) $b['delai_jours']);

        foreach ($etapes as $i => $etape) {
            RelanceEtape::create([
                'relance_scenario_id' => $scenario->id,
                'numero_ordre'        => $i + 1,
                'delai_jours'         => $etape['delai_jours'],
                'sujet'               => $etape['sujet'],
                'corps_email'         => $etape['corps_email'],
                'canal'               => $etape['canal'],
                'ton'                 => $etape['ton'],
                'actif'               => isset($etape['actif']) ? (bool) $etape['actif'] : true,
            ]);
        }
    }
}
