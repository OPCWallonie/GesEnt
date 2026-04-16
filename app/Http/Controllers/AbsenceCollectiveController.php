<?php

namespace App\Http\Controllers;

use App\Models\AbsenceCollective;
use App\Models\Ouvrier;
use App\Services\AbsenceCollectiveService;
use Illuminate\Http\Request;

class AbsenceCollectiveController extends Controller
{
    public function __construct(private AbsenceCollectiveService $service) {}

    public function index(Request $request)
    {
        $annee = (int) $request->get('annee', now()->year);

        $absencesParType = AbsenceCollective::whereYear('date', $annee)
            ->orderBy('date')
            ->get()
            ->groupBy('type_collectif');

        $soldeRcParPersonne = Ouvrier::planifiable()
            ->where('actif', true)
            ->get()
            ->map(fn($o) => [
                'ouvrier'  => $o,
                'quota'    => $o->quota_rc_annuel,
                'utilises' => $o->reposCompensatoiresUtilises($annee),
                'restants' => $o->reposCompensatoiresRestants($annee),
            ])
            ->filter(fn($item) => $item['quota'] > 0);

        return view('absences-collectives.index', compact('absencesParType', 'soldeRcParPersonne', 'annee'));
    }

    public function create(Request $request)
    {
        $commissions    = Ouvrier::COMMISSIONS_PARITAIRES;
        $typesPersonnel = Ouvrier::TYPES_PERSONNEL;
        $typeDefaut     = $request->get('type', 'repos_compensatoire');

        return view('absences-collectives.create', compact('commissions', 'typesPersonnel', 'typeDefaut'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAc($request);

        AbsenceCollective::create($data);

        return redirect()->route('absences-collectives.index')
                         ->with('success', 'Absence collective créée.');
    }

    public function show(AbsenceCollective $absenceCollective)
    {
        $absenceCollective->load('absences.ouvrier');

        $conflits  = $absenceCollective->applique ? collect() : $absenceCollective->detecterConflits();
        $personnel = $absenceCollective->applique
            ? collect()
            : $absenceCollective->personnelConcerne();

        return view('absences-collectives.show', compact('absenceCollective', 'conflits', 'personnel'));
    }

    public function appliquer(AbsenceCollective $absenceCollective)
    {
        if ($absenceCollective->applique) {
            return back()->with('error', 'Cette absence collective est déjà appliquée.');
        }

        $result = $this->service->appliquer($absenceCollective);

        $msg = "{$result['crees']} absence(s) créée(s).";
        if ($result['ignores'] > 0) {
            $msg .= " {$result['ignores']} conflit(s) ignoré(s).";
        }

        return redirect()->route('absences-collectives.show', $absenceCollective)
                         ->with('success', $msg);
    }

    public function annuler(AbsenceCollective $absenceCollective)
    {
        if (! $absenceCollective->applique) {
            return back()->with('error', 'Cette absence collective n\'est pas encore appliquée.');
        }

        $count = $this->service->annuler($absenceCollective);

        return redirect()->route('absences-collectives.show', $absenceCollective)
                         ->with('success', "{$count} absence(s) supprimée(s). Absence collective annulée.");
    }

    public function destroy(AbsenceCollective $absenceCollective)
    {
        if ($absenceCollective->applique) {
            return back()->with('error', 'Annulez d\'abord avant de supprimer.');
        }

        $absenceCollective->delete();

        return redirect()->route('absences-collectives.index')
                         ->with('success', 'Absence collective supprimée.');
    }

    // ─── Validation ──────────────────────────────────────────────

    private function validateAc(Request $request): array
    {
        $data = $request->validate([
            'type_collectif'      => ['required', 'in:' . implode(',', array_keys(AbsenceCollective::TYPES_COLLECTIFS))],
            'libelle'             => ['required', 'string', 'max:150'],
            'date'                => ['required', 'date'],
            'demi_journee'        => ['boolean'],
            'perimetre'           => ['required', 'in:tous,cp,type'],
            'perimetre_valeurs'   => ['nullable', 'array'],
            'perimetre_valeurs.*' => ['string'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['perimetre'] === 'tous') {
            $data['perimetre_valeurs'] = null;
        }

        $data['demi_journee'] = $request->boolean('demi_journee');

        return $data;
    }
}
