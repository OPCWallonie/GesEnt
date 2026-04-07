<?php
// Gesent2026 ProduitController
namespace App\Http\Controllers;

use App\Models\CatalogProduit;
use App\Models\Produit;
use App\Models\ProduitAssociation;
use App\Models\ProduitUsageStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $produits = Produit::query()
            ->when($request->q, fn($q, $s) => $q->where('designation', 'like', '%' . like_escape($s) . '%'))
            ->when($request->categorie, fn($q, $c) => $q->where('categorie', $c))
            ->orderBy('designation')
            ->paginate(20)
            ->withQueryString();

        $categories = Produit::whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        return view('produits.index', compact('produits', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Produit::whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        return view('produits.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reference'             => 'nullable|string|max:50|unique:produits,reference',
            'designation'           => 'required|string|max:255',
            'description'           => 'nullable|string',
            'unite'                 => 'required|string|max:20',
            'prix_unitaire'         => 'required|numeric|min:0',
            'taux_tva'              => 'required|in:0,6,12,21',
            'categorie'             => 'nullable|string|max:100',
            'fournisseur'           => 'nullable|string|max:100',
            'reference_fournisseur' => 'nullable|string|max:50',
        ]);

        $produit = Produit::create($data);

        return redirect()->route('produits.index')
            ->with('success', "Produit « {$produit->designation} » créé.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit)
    {
        return redirect()->route('produits.edit', $produit);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produit $produit)
    {
        $categories = Produit::whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        return view('produits.edit', compact('produit', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produit $produit)
    {
        $data = $request->validate([
            'reference'             => 'nullable|string|max:50|unique:produits,reference,' . $produit->id,
            'designation'           => 'required|string|max:255',
            'description'           => 'nullable|string',
            'unite'                 => 'required|string|max:20',
            'prix_unitaire'         => 'required|numeric|min:0',
            'taux_tva'              => 'required|in:0,6,12,21',
            'categorie'             => 'nullable|string|max:100',
            'fournisseur'           => 'nullable|string|max:100',
            'reference_fournisseur' => 'nullable|string|max:50',
        ]);

        $produit->update($data);

        return redirect()->route('produits.index')
            ->with('success', 'Produit mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit)
    {
        $produit->delete();

        return redirect()->route('produits.index')
            ->with('success', "Produit « {$produit->designation} » supprimé.");
    }

    /**
     * Import produits depuis un CSV.
     * Colonnes attendues (séparateur ; ou ,) :
     * reference, designation, unite, prix_unitaire, taux_tva, categorie, fournisseur, reference_fournisseur
     */
    public function import(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $fichier  = $request->file('fichier');
        $handle   = fopen($fichier->getPathname(), 'r');
        $entetes  = null;
        $inseres  = 0;
        $ignores  = 0;
        $erreurs  = [];

        while (($ligne = fgetcsv($handle, 0, $this->detecterSeparateur($fichier->getPathname()))) !== false) {
            // Première ligne = entêtes
            if ($entetes === null) {
                $entetes = array_map('strtolower', array_map('trim', $ligne));
                continue;
            }

            if (count($ligne) < 2) continue;

            $row = array_combine(
                array_slice($entetes, 0, count($ligne)),
                array_map('trim', $ligne)
            );

            $designation = $row['designation'] ?? null;
            if (! $designation) { $ignores++; continue; }

            $reference = $row['reference'] ?? null;

            // Éviter les doublons sur référence
            if ($reference && Produit::where('reference', $reference)->exists()) {
                $ignores++;
                continue;
            }

            try {
                Produit::create([
                    'reference'             => $reference ?: null,
                    'designation'           => $designation,
                    'unite'                 => $row['unite'] ?? 'pièce',
                    'prix_unitaire'         => (float) str_replace(',', '.', $row['prix_unitaire'] ?? 0),
                    'taux_tva'              => in_array((int)($row['taux_tva'] ?? 21), [0, 6, 12, 21]) ? (int)($row['taux_tva'] ?? 21) : 21,
                    'categorie'             => $row['categorie'] ?? null,
                    'fournisseur'           => $row['fournisseur'] ?? null,
                    'reference_fournisseur' => $row['reference_fournisseur'] ?? null,
                ]);
                $inseres++;
            } catch (\Exception $e) {
                $erreurs[] = "Ligne ignorée : {$designation} ({$e->getMessage()})";
            }
        }

        fclose($handle);

        $msg = "{$inseres} produit(s) importé(s)";
        if ($ignores > 0) $msg .= ", {$ignores} ignoré(s)";
        if ($erreurs)     $msg .= '. Erreurs : ' . implode(' | ', array_slice($erreurs, 0, 3));

        return redirect()->route('produits.index')->with('success', $msg);
    }

    private function detecterSeparateur(string $path): string
    {
        $ligne = fgets(fopen($path, 'r'));
        return (substr_count($ligne, ';') >= substr_count($ligne, ',')) ? ';' : ',';
    }

    /**
     * API endpoint for autocomplete search — triée par score d'usage.
     */
    public function search(Request $request)
    {
        $q = $request->string('q')->toString();

        $results = Produit::actif()
            ->where('produits.designation', 'like', '%' . like_escape($q) . '%')
            ->leftJoin('produit_usage_stats', 'produits.id', '=', 'produit_usage_stats.produit_id')
            ->select('produits.id', 'produits.designation', 'produits.prix_unitaire', 'produits.taux_tva', 'produits.unite',
                     DB::raw('COALESCE(produit_usage_stats.score, 0) as usage_score'))
            ->orderByDesc('usage_score')
            ->orderBy('produits.designation')
            ->limit(10)
            ->get();

        return response()->json($results->map(fn($p) => [
            'id'          => $p->id,
            'designation' => $p->designation,
            'prix'        => (float) $p->prix_unitaire,
            'prix_unitaire'=> (float) $p->prix_unitaire,
            'taux_tva'    => (float) $p->taux_tva,
            'unite'       => $p->unite,
            'score'       => (float) $p->usage_score,
            'habituel'    => $p->usage_score > 5,
            'source'      => 'interne',
        ]));
    }

    /**
     * Suggestions intelligentes basées sur les produits déjà dans le document.
     */
    public function suggestions(Request $request)
    {
        $produitsActuels = $request->input('produits', []);
        $q               = trim($request->get('q', ''));

        // Pas de contexte et pas de recherche → top produits habituels
        if (empty($produitsActuels) && strlen($q) < 2) {
            return $this->topProduits();
        }

        // Collecter les produits associés aux produits déjà présents
        $associes = collect();
        foreach ($produitsActuels as $key) {
            foreach (ProduitAssociation::associesDe($key, 20) as $id) {
                $associes->push($id);
            }
        }
        $scoredAssocies = $associes->countBy()->sortDesc();

        // Si recherche textuelle
        if (strlen($q) >= 2) {
            $like = '%' . like_escape($q) . '%';
            $resultats = collect();

            $interneIds = $scoredAssocies->keys()->filter(fn($k) => str_starts_with($k, 'p:'))
                ->map(fn($k) => (int) substr($k, 2));
            $catalogIds = $scoredAssocies->keys()->filter(fn($k) => str_starts_with($k, 'c:'))
                ->map(fn($k) => (int) substr($k, 2));

            if ($interneIds->isNotEmpty()) {
                Produit::whereIn('id', $interneIds)->where('designation', 'like', $like)
                    ->get()->each(function ($p) use (&$resultats, $scoredAssocies) {
                        $resultats->push([
                            'id' => $p->id, 'designation' => $p->designation,
                            'prix' => (float) $p->prix_unitaire, 'prix_unitaire' => (float) $p->prix_unitaire,
                            'taux_tva' => (float) $p->taux_tva, 'unite' => $p->unite,
                            'score' => $scoredAssocies->get('p:' . $p->id, 0) * 100,
                            'source' => 'interne', 'associe' => true,
                        ]);
                    });
            }

            if ($catalogIds->isNotEmpty()) {
                CatalogProduit::whereIn('id', $catalogIds)->where('designation', 'like', $like)
                    ->get()->each(function ($p) use (&$resultats, $scoredAssocies) {
                        $resultats->push([
                            'id' => $p->id, 'fournisseur' => $p->nom_fournisseur ?? $p->fournisseur,
                            'reference' => $p->reference,
                            'designation' => $p->designation . ($p->marque ? " ({$p->marque})" : ''),
                            'unite' => $p->unite, 'prix' => (float) $p->prix_revente,
                            'prix_base' => (float) $p->prix_catalogue, 'taux_tva' => (float) $p->taux_tva,
                            'en_stock' => $p->en_stock,
                            'score' => $scoredAssocies->get('c:' . $p->id, 0) * 100,
                            'source' => 'catalogue', 'associe' => true,
                        ]);
                    });
            }

            // Compléter avec la recherche textuelle standard (produits non associés)
            $dejaDans = $resultats->pluck('id')->toArray();
            Produit::actif()->where('produits.designation', 'like', $like)
                ->leftJoin('produit_usage_stats', 'produits.id', '=', 'produit_usage_stats.produit_id')
                ->select('produits.id', 'produits.designation', 'produits.prix_unitaire',
                         'produits.taux_tva', 'produits.unite',
                         DB::raw('COALESCE(produit_usage_stats.score, 0) as usage_score'))
                ->orderByDesc('usage_score')
                ->limit(15)
                ->get()
                ->each(function ($p) use (&$resultats, $dejaDans) {
                    if (in_array($p->id, $dejaDans)) return;
                    $resultats->push([
                        'id' => $p->id, 'designation' => $p->designation,
                        'prix' => (float) $p->prix_unitaire, 'prix_unitaire' => (float) $p->prix_unitaire,
                        'taux_tva' => (float) $p->taux_tva, 'unite' => $p->unite,
                        'score' => (float) $p->usage_score,
                        'habituel' => $p->usage_score > 5,
                        'source' => 'interne',
                    ]);
                });

            return response()->json($resultats->sortByDesc('score')->values()->take(15));
        }

        // Pas de recherche textuelle → top associés
        $resultats = collect();
        foreach ($scoredAssocies->take(10) as $key => $count) {
            if (str_starts_with($key, 'p:')) {
                $p = Produit::find((int) substr($key, 2));
                if (!$p) continue;
                $resultats->push([
                    'id' => $p->id, 'designation' => $p->designation,
                    'prix' => (float) $p->prix_unitaire, 'prix_unitaire' => (float) $p->prix_unitaire,
                    'taux_tva' => (float) $p->taux_tva, 'unite' => $p->unite,
                    'score' => $count * 100, 'source' => 'interne', 'associe' => true,
                ]);
            } elseif (str_starts_with($key, 'c:')) {
                $p = CatalogProduit::find((int) substr($key, 2));
                if (!$p) continue;
                $resultats->push([
                    'id' => $p->id, 'fournisseur' => $p->nom_fournisseur ?? $p->fournisseur,
                    'reference' => $p->reference,
                    'designation' => $p->designation . ($p->marque ? " ({$p->marque})" : ''),
                    'unite' => $p->unite, 'prix' => (float) $p->prix_revente,
                    'prix_base' => (float) $p->prix_catalogue, 'taux_tva' => (float) $p->taux_tva,
                    'en_stock' => $p->en_stock,
                    'score' => $count * 100, 'source' => 'catalogue', 'associe' => true,
                ]);
            }
        }

        return response()->json($resultats->values());
    }

    private function topProduits()
    {
        $stats    = ProduitUsageStat::orderByDesc('score')->take(10)->get();
        $resultats = [];

        foreach ($stats as $stat) {
            if ($stat->produit_id && $stat->produit) {
                $p = $stat->produit;
                $resultats[] = [
                    'id' => $p->id, 'designation' => $p->designation,
                    'prix' => (float) $p->prix_unitaire, 'prix_unitaire' => (float) $p->prix_unitaire,
                    'taux_tva' => (float) $p->taux_tva, 'unite' => $p->unite,
                    'score' => (float) $stat->score, 'source' => 'interne', 'habituel' => true,
                ];
            } elseif ($stat->catalog_produit_id && $stat->catalogProduit) {
                $p = $stat->catalogProduit;
                $resultats[] = [
                    'id' => $p->id, 'fournisseur' => $p->nom_fournisseur ?? $p->fournisseur,
                    'reference' => $p->reference,
                    'designation' => $p->designation . ($p->marque ? " ({$p->marque})" : ''),
                    'unite' => $p->unite, 'prix' => (float) $p->prix_revente,
                    'prix_base' => (float) $p->prix_catalogue, 'taux_tva' => (float) $p->taux_tva,
                    'score' => (float) $stat->score, 'source' => 'catalogue', 'habituel' => true,
                ];
            }
        }

        return response()->json($resultats);
    }
}
