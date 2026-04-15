<?php

namespace App\Http\Controllers;

use App\Models\Ouvrier;
use App\Models\ReposCollectif;
use App\Services\ReposCollectifService;
use Illuminate\Http\Request;

class ReposCollectifController extends Controller
{
    public function __construct(private ReposCollectifService $service) {}

    public function index()
    {
        $reposCollectifs = ReposCollectif::orderBy('date', 'desc')->paginate(20);

        return view('repos-collectifs.index', compact('reposCollectifs'));
    }

    public function create()
    {
        $commissions = \App\Models\Ouvrier::COMMISSIONS_PARITAIRES;
        $typesPersonnel = \App\Models\Ouvrier::TYPES_PERSONNEL;

        return view('repos-collectifs.create', compact('commissions', 'typesPersonnel'));
    }

    public function store(Request $request)
    {
        $data = $this->validateRc($request);

        ReposCollectif::create($data);

        return redirect()->route('repos-collectifs.index')
                         ->with('success', 'Repos compensatoire collectif créé.');
    }

    public function show(ReposCollectif $reposCollectif)
    {
        $reposCollectif->load('absences.ouvrier');

        $conflits   = $reposCollectif->applique ? collect() : $reposCollectif->detecterConflits();
        $personnel  = $reposCollectif->applique
            ? collect()
            : $reposCollectif->personnelConcerne();

        return view('repos-collectifs.show', compact('reposCollectif', 'conflits', 'personnel'));
    }

    public function appliquer(ReposCollectif $reposCollectif)
    {
        if ($reposCollectif->applique) {
            return back()->with('error', 'Ce RC est déjà appliqué.');
        }

        $result = $this->service->appliquer($reposCollectif);

        $msg = "{$result['crees']} absence(s) créée(s).";
        if ($result['ignores'] > 0) {
            $msg .= " {$result['ignores']} conflit(s) ignoré(s).";
        }

        return redirect()->route('repos-collectifs.show', $reposCollectif)
                         ->with('success', $msg);
    }

    public function annuler(ReposCollectif $reposCollectif)
    {
        if (! $reposCollectif->applique) {
            return back()->with('error', 'Ce RC n\'est pas encore appliqué.');
        }

        $count = $this->service->annuler($reposCollectif);

        return redirect()->route('repos-collectifs.show', $reposCollectif)
                         ->with('success', "{$count} absence(s) supprimée(s). RC annulé.");
    }

    public function destroy(ReposCollectif $reposCollectif)
    {
        if ($reposCollectif->applique) {
            return back()->with('error', 'Annulez d\'abord le RC avant de le supprimer.');
        }

        $reposCollectif->delete();

        return redirect()->route('repos-collectifs.index')
                         ->with('success', 'Repos compensatoire collectif supprimé.');
    }

    public function importerForm()
    {
        return view('repos-collectifs.importer');
    }

    public function importer(Request $request)
    {
        $request->validate([
            'fichier_csv' => ['required', 'file', 'mimes:csv,txt', 'max:512'],
        ]);

        $content = file_get_contents($request->file('fichier_csv')->getRealPath());
        // Normaliser les fins de lignes
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Ignorer la première ligne si c'est un en-tête
        $lignes = explode("\n", trim($content));
        if (count($lignes) > 0) {
            $premiere = strtolower(trim($lignes[0]));
            if (str_contains($premiere, 'date') || str_contains($premiere, 'libel')) {
                array_shift($lignes);
                $content = implode("\n", $lignes);
            }
        }

        $result = $this->service->importerCalendrier($content);

        $msg = "{$result['crees']} RC importé(s).";
        if (! empty($result['erreurs'])) {
            $msg .= ' ' . count($result['erreurs']) . ' ligne(s) ignorée(s).';
        }

        return redirect()->route('repos-collectifs.index')
                         ->with('success', $msg)
                         ->with('import_erreurs', $result['erreurs']);
    }

    // ─── Validation ──────────────────────────────────────────────

    private function validateRc(Request $request): array
    {
        $data = $request->validate([
            'libelle'          => ['required', 'string', 'max:150'],
            'date'             => ['required', 'date'],
            'demi_journee'     => ['boolean'],
            'perimetre'        => ['required', 'in:tous,cp,type'],
            'perimetre_valeurs' => ['nullable', 'array'],
            'perimetre_valeurs.*' => ['string'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        // Nettoyer perimetre_valeurs si perimetre = tous
        if ($data['perimetre'] === 'tous') {
            $data['perimetre_valeurs'] = null;
        }

        $data['demi_journee'] = $request->boolean('demi_journee');

        return $data;
    }
}
