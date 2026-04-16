<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Ouvrier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OuvrierController extends Controller
{
    public function index(Request $request)
    {
        $query = Ouvrier::withCount(['pointages'])
            ->with('absenceActuelle')
            ->withTrashed(false);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($s) => $s->where('nom', 'like', "%$q%")->orWhere('prenom', 'like', "%$q%"));
        }

        if ($request->filled('type_personnel')) {
            $query->where('type_personnel', $request->type_personnel);
        }

        if ($request->filled('commission_paritaire')) {
            $query->where('commission_paritaire', $request->commission_paritaire);
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if (! $request->boolean('desactives')) {
            $query->where('actif', true);
        }

        $ouvriers = $query->orderBy('nom')->orderBy('prenom')->paginate(25)->withQueryString();

        return view('ouvriers.index', compact('ouvriers'));
    }

    public function create()
    {
        $ouvrier = new Ouvrier([
            'date_entree'          => today(),
            'type_personnel'       => 'ouvrier',
            'commission_paritaire' => 'CP124',
            'heures_semaine'       => 40,
        ]);
        $certificationTypes = Certification::TYPES;
        return view('ouvriers.edit', compact('ouvrier', 'certificationTypes'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePersonnel($request);

        $data['actif'] = $request->boolean('actif', true);

        // Désactivation : date et motif obligatoires
        if (! $data['actif']) {
            $request->validate([
                'date_sortie'   => 'required|date',
                'motif_sortie'  => ['required', Rule::in(array_keys(Ouvrier::MOTIFS_SORTIE))],
            ]);
            $data['date_sortie']  = $request->date_sortie;
            $data['motif_sortie'] = $request->motif_sortie;
        } else {
            $data['date_sortie']  = null;
            $data['motif_sortie'] = null;
        }

        $ouvrier = Ouvrier::create($data);
        $this->syncCertifications($ouvrier, $request->input('certifications', []));

        return redirect()->route('ouvriers.show', $ouvrier)
            ->with('success', "Membre du personnel {$ouvrier->nom_complet} créé.");
    }

    public function show(Ouvrier $ouvrier)
    {
        $ouvrier->load(['pointages.chantier', 'absences', 'certifications']);

        $lundi    = now()->startOfWeek();
        $heureSem = $ouvrier->pointages()
            ->whereBetween('date', [$lundi, $lundi->copy()->addDays(6)])
            ->selectRaw('SUM(heures + heures_sup) as total')
            ->value('total') ?? 0;

        $coutAnnee = $ouvrier->coutTotal(now()->year);

        $derniersPointages = $ouvrier->pointages()
            ->with('chantier')
            ->orderByDesc('date')
            ->limit(20)
            ->get();

        $absencesActives = $ouvrier->absences()
            ->where('date_fin', '>=', today())
            ->orderBy('date_debut')
            ->get();

        $reposRestants       = $ouvrier->reposCompensatoiresRestants(now()->year);
        $heuresRecupAccum    = $ouvrier->heuresRecuperablesAccumulees(now()->year);
        $heuresRecupConso    = $ouvrier->heuresRecupereesConsommees(now()->year);
        $soldeRecup          = $ouvrier->soldeRecuperation(now()->year);

        $resumeAbsences = $ouvrier->absences()
            ->whereYear('date_debut', now()->year)
            ->get()
            ->groupBy('type')
            ->map(fn($groupe) => [
                'count'   => $groupe->count(),
                'jours'   => $groupe->sum(fn($a) => $a->nb_jours),
                'libelle' => $groupe->first()->libelle_type,
            ]);

        $bradfordFactor = $ouvrier->bradfordFactor(now()->year);

        $certificationsAlerte = $ouvrier->certifications
            ->filter(fn($c) => $c->est_expiree || $c->expire_bientot);

        $congesPayesUtilises  = $ouvrier->congesPayesUtilises(now()->year);
        $congesPayesRestants  = $ouvrier->congesPayesRestants(now()->year);
        $congesLegaux         = $ouvrier->congesLegauxUtilises(now()->year);
        $congesAnciennete     = $ouvrier->congesAncienneteUtilises(now()->year);

        return view('ouvriers.show', compact(
            'ouvrier', 'heureSem', 'coutAnnee',
            'derniersPointages', 'absencesActives', 'reposRestants',
            'resumeAbsences', 'bradfordFactor', 'certificationsAlerte',
            'heuresRecupAccum', 'heuresRecupConso', 'soldeRecup',
            'congesPayesUtilises', 'congesPayesRestants', 'congesLegaux', 'congesAnciennete'
        ));
    }

    public function edit(Ouvrier $ouvrier)
    {
        $ouvrier->load('certifications');
        $certificationTypes = Certification::TYPES;
        return view('ouvriers.edit', compact('ouvrier', 'certificationTypes'));
    }

    public function update(Request $request, Ouvrier $ouvrier)
    {
        $data = $this->validatePersonnel($request, $ouvrier->id);

        $data['actif'] = $request->boolean('actif', true);

        if (! $data['actif']) {
            $request->validate([
                'date_sortie'   => 'required|date',
                'motif_sortie'  => ['required', Rule::in(array_keys(Ouvrier::MOTIFS_SORTIE))],
            ]);
            $data['date_sortie']  = $request->date_sortie;
            $data['motif_sortie'] = $request->motif_sortie;
        } else {
            // Réactivation : nettoyer les champs de sortie
            $data['date_sortie']  = null;
            $data['motif_sortie'] = null;
        }

        $ouvrier->update($data);
        $this->syncCertifications($ouvrier, $request->input('certifications', []));

        return redirect()->route('ouvriers.show', $ouvrier)
            ->with('success', 'Membre du personnel mis à jour.');
    }

    public function destroy(Ouvrier $ouvrier)
    {
        $ouvrier->delete();
        return redirect()->route('ouvriers.index')
            ->with('success', 'Membre du personnel archivé.');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Validation commune store/update.
     * $ouvrierId fourni pour l'unicité du numéro national en update.
     */
    private function validatePersonnel(Request $request, ?int $ouvrierId = null): array
    {
        $typePersonnel = $request->input('type_personnel', 'ouvrier');
        $cp            = $request->input('commission_paritaire', 'CP124');

        $data = $request->validate([
            'type_personnel'       => ['required', Rule::in(array_keys(Ouvrier::TYPES_PERSONNEL))],
            'nom'                  => 'required|string|max:100',
            'prenom'               => 'required|string|max:100',
            'numero_national'      => [
                'nullable', 'string', 'max:20',
                $ouvrierId
                    ? Rule::unique('ouvriers', 'numero_national')->ignore($ouvrierId)
                    : Rule::unique('ouvriers', 'numero_national'),
            ],
            'commission_paritaire' => ['required', Rule::in(array_keys(Ouvrier::COMMISSIONS_PARITAIRES))],
            'categorie'            => 'nullable|string|max:10',
            'cout_horaire'         => 'nullable|numeric|min:0',
            'cout_mensuel'         => 'nullable|numeric|min:0',
            'heures_semaine'                => 'required|numeric|min:20|max:50',
            'mode_heures_sup_defaut'        => 'nullable|in:payees,recuperees',
            'jours_conges_supplementaires'  => 'nullable|integer|min:0|max:20',
            'date_entree'          => 'required|date',
            'telephone'            => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'notes'                => 'nullable|string',
            'metier'               => 'nullable|string|max:100',
        ]);

        // Catégorie : null si la CP n'en a pas, ou si la valeur reçue n'est pas dans la liste
        $cpCats = Ouvrier::CATEGORIES_PAR_CP[$cp] ?? [];
        if (empty($cpCats)) {
            $data['categorie'] = null;
        } elseif (! empty($data['categorie']) && ! in_array($data['categorie'], $cpCats)) {
            $data['categorie'] = null;
        }

        // Pour employé admin / direction : le coût se saisit en mensuel.
        // Si cout_horaire est absent ou vide, forcer null — l'accesseur
        // cout_horaire_effectif fera la conversion depuis cout_mensuel.
        if (in_array($typePersonnel, ['employe_admin', 'direction'])) {
            if (empty($data['cout_horaire'])) {
                $data['cout_horaire'] = null;
            }
        }

        return $data;
    }

    private function syncCertifications(Ouvrier $ouvrier, array $certifications): void
    {
        $idsGardes = [];

        foreach ($certifications as $row) {
            if (empty($row['type'])) {
                continue;
            }

            $type           = $row['type'];
            $dateObtention  = $row['date_obtention'] ?? null;
            $dateExpiration = $row['date_expiration'] ?? null ?: null;

            if (! $dateObtention || ! array_key_exists($type, Certification::TYPES)) {
                continue;
            }

            if (! empty($row['id'])) {
                $cert = $ouvrier->certifications()->find($row['id']);
                if ($cert) {
                    $cert->update([
                        'type'              => $type,
                        'date_obtention'    => $dateObtention,
                        'date_expiration'   => $dateExpiration,
                        'organisme'         => $row['organisme'] ?? null,
                        'numero_certificat' => $row['numero_certificat'] ?? null,
                        'notes'             => $row['notes'] ?? null,
                    ]);
                    $idsGardes[] = $cert->id;
                }
            } else {
                $cert = $ouvrier->certifications()->updateOrCreate(
                    ['type' => $type],
                    [
                        'date_obtention'    => $dateObtention,
                        'date_expiration'   => $dateExpiration,
                        'organisme'         => $row['organisme'] ?? null,
                        'numero_certificat' => $row['numero_certificat'] ?? null,
                        'notes'             => $row['notes'] ?? null,
                    ]
                );
                $idsGardes[] = $cert->id;
            }
        }

        if (! empty($idsGardes)) {
            $ouvrier->certifications()->whereNotIn('id', $idsGardes)->delete();
        }
    }

    public function apiSearch(Request $request)
    {
        $q     = $request->get('q', '');
        $actif = $request->boolean('actif', true);

        $results = Ouvrier::where('actif', $actif)
            ->where(fn($s) => $s->where('nom', 'like', "%$q%")->orWhere('prenom', 'like', "%$q%"))
            ->orderBy('nom')->orderBy('prenom')
            ->limit(20)
            ->get(['id', 'nom', 'prenom', 'categorie', 'cout_horaire', 'cout_mensuel'])
            ->map(fn($o) => [
                'id'            => $o->id,
                'nom_complet'   => $o->nom_complet,
                'categorie'     => $o->categorie,
                'cout_horaire'  => $o->cout_horaire_effectif,
            ]);

        return response()->json($results);
    }
}
