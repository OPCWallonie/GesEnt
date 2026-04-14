<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Ouvrier;
use Illuminate\Http\Request;

class OuvrierController extends Controller
{
    public function index(Request $request)
    {
        $query = Ouvrier::withCount(['pointages'])
            ->withTrashed(false);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($s) => $s->where('nom', 'like', "%$q%")->orWhere('prenom', 'like', "%$q%"));
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if ($request->boolean('actifs_seulement')) {
            $query->where('actif', true);
        }

        $ouvriers = $query->orderBy('nom')->orderBy('prenom')->paginate(25)->withQueryString();

        return view('ouvriers.index', compact('ouvriers'));
    }

    public function create()
    {
        $ouvrier = new Ouvrier(['date_entree' => today()]);
        $certificationTypes = Certification::TYPES;
        return view('ouvriers.edit', compact('ouvrier', 'certificationTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'             => 'required|string|max:100',
            'prenom'          => 'required|string|max:100',
            'numero_national' => 'nullable|string|max:20|unique:ouvriers,numero_national',
            'categorie'       => 'required|in:' . implode(',', \App\Models\Ouvrier::CATEGORIES),
            'cout_horaire'    => 'required|numeric|min:0',
            'date_entree'     => 'required|date',
            'date_sortie'     => 'nullable|date|after_or_equal:date_entree',
            'telephone'       => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:100',
            'notes'           => 'nullable|string',
            'metier'          => 'nullable|string|max:100',
        ]);

        $data['actif'] = $request->boolean('actif', true);
        $ouvrier = Ouvrier::create($data);

        $this->syncCertifications($ouvrier, $request->input('certifications', []));

        return redirect()->route('ouvriers.show', $ouvrier)
            ->with('success', "Ouvrier {$ouvrier->nom_complet} créé.");
    }

    public function show(Ouvrier $ouvrier)
    {
        $ouvrier->load(['pointages.chantier', 'absences', 'certifications']);

        // Semaine en cours pour le résumé
        $lundi    = now()->startOfWeek();
        $heureSem = $ouvrier->pointages()
            ->whereBetween('date', [$lundi, $lundi->copy()->addDays(6)])
            ->selectRaw('SUM(heures + heures_sup) as total')
            ->value('total') ?? 0;

        // Coût YTD
        $coutAnnee = $ouvrier->coutTotal(now()->year);

        // Derniers pointages
        $derniersPointages = $ouvrier->pointages()
            ->with('chantier')
            ->orderByDesc('date')
            ->limit(20)
            ->get();

        // Absences en cours / à venir
        $absencesActives = $ouvrier->absences()
            ->where('date_fin', '>=', today())
            ->orderBy('date_debut')
            ->get();

        $reposRestants = $ouvrier->reposCompensatoiresRestants(now()->year);

        // Résumé absences par type (année en cours)
        $resumeAbsences = $ouvrier->absences()
            ->whereYear('date_debut', now()->year)
            ->get()
            ->groupBy('type')
            ->map(fn($groupe) => [
                'count'  => $groupe->count(),
                'jours'  => $groupe->sum(fn($a) => $a->nb_jours),
                'libelle' => $groupe->first()->libelle_type,
            ]);

        // Bradford Factor (maladie uniquement)
        $bradfordFactor = $ouvrier->bradfordFactor(now()->year);

        // Certifications à renouveler
        $certificationsAlerte = $ouvrier->certifications
            ->filter(fn($c) => $c->est_expiree || $c->expire_bientot);

        return view('ouvriers.show', compact(
            'ouvrier', 'heureSem', 'coutAnnee',
            'derniersPointages', 'absencesActives', 'reposRestants',
            'resumeAbsences', 'bradfordFactor', 'certificationsAlerte'
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
        $data = $request->validate([
            'nom'             => 'required|string|max:100',
            'prenom'          => 'required|string|max:100',
            'numero_national' => "nullable|string|max:20|unique:ouvriers,numero_national,{$ouvrier->id}",
            'categorie'       => 'required|in:' . implode(',', \App\Models\Ouvrier::CATEGORIES),
            'cout_horaire'    => 'required|numeric|min:0',
            'date_entree'     => 'required|date',
            'date_sortie'     => 'nullable|date|after_or_equal:date_entree',
            'telephone'       => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:100',
            'notes'           => 'nullable|string',
            'metier'          => 'nullable|string|max:100',
        ]);

        $data['actif'] = $request->boolean('actif', true);
        $ouvrier->update($data);

        $this->syncCertifications($ouvrier, $request->input('certifications', []));

        return redirect()->route('ouvriers.show', $ouvrier)
            ->with('success', 'Ouvrier mis à jour.');
    }

    public function destroy(Ouvrier $ouvrier)
    {
        $ouvrier->delete();
        return redirect()->route('ouvriers.index')
            ->with('success', 'Ouvrier archivé.');
    }

    private function syncCertifications(Ouvrier $ouvrier, array $certifications): void
    {
        // IDs soumis (existants)
        $idsGardes = [];

        foreach ($certifications as $row) {
            if (empty($row['type'])) {
                continue;
            }

            // Validation minimale
            $type           = $row['type'];
            $dateObtention  = $row['date_obtention'] ?? null;
            $dateExpiration = $row['date_expiration'] ?? null ?: null;

            if (! $dateObtention || ! array_key_exists($type, Certification::TYPES)) {
                continue;
            }

            if (! empty($row['id'])) {
                // Mise à jour
                $cert = $ouvrier->certifications()->find($row['id']);
                if ($cert) {
                    $cert->update([
                        'type'             => $type,
                        'date_obtention'   => $dateObtention,
                        'date_expiration'  => $dateExpiration,
                        'organisme'        => $row['organisme'] ?? null,
                        'numero_certificat'=> $row['numero_certificat'] ?? null,
                        'notes'            => $row['notes'] ?? null,
                    ]);
                    $idsGardes[] = $cert->id;
                }
            } else {
                // Création (updateOrCreate sur type pour respecter la contrainte unique)
                $cert = $ouvrier->certifications()->updateOrCreate(
                    ['type' => $type],
                    [
                        'date_obtention'   => $dateObtention,
                        'date_expiration'  => $dateExpiration,
                        'organisme'        => $row['organisme'] ?? null,
                        'numero_certificat'=> $row['numero_certificat'] ?? null,
                        'notes'            => $row['notes'] ?? null,
                    ]
                );
                $idsGardes[] = $cert->id;
            }
        }

        // Supprimer les certifications qui n'ont pas été soumises
        if (! empty($idsGardes)) {
            $ouvrier->certifications()->whereNotIn('id', $idsGardes)->delete();
        } else {
            // Aucune certification soumise = tout supprimer (si le formulaire était vide)
            // On ne supprime que si le champ certifications était explicitement soumis
            // (le tableau peut être absent si JS désactivé — dans ce cas on ne touche pas)
        }
    }

    public function apiSearch(Request $request)
    {
        $q       = $request->get('q', '');
        $actif   = $request->boolean('actif', true);
        $results = Ouvrier::where('actif', $actif)
            ->where(fn($s) => $s->where('nom', 'like', "%$q%")->orWhere('prenom', 'like', "%$q%"))
            ->orderBy('nom')->orderBy('prenom')
            ->limit(20)
            ->get(['id', 'nom', 'prenom', 'categorie', 'cout_horaire'])
            ->map(fn($o) => [
                'id'           => $o->id,
                'nom_complet'  => $o->nom_complet,
                'categorie'    => $o->categorie,
                'cout_horaire' => $o->cout_horaire,
            ]);

        return response()->json($results);
    }
}
