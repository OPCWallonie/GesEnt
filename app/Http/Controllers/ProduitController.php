<?php
// Gesent2026 ProduitController
namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $produits = Produit::query()
            ->when($request->q, fn($q, $s) => $q->where('designation', 'like', "%{$s}%"))
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
     * API endpoint for autocomplete search.
     */
    public function search(Request $request)
    {
        $results = Produit::actif()
            ->where('designation', 'like', '%' . $request->q . '%')
            ->orderBy('designation')
            ->limit(10)
            ->get(['id', 'designation', 'prix_unitaire', 'taux_tva', 'unite']);

        return response()->json($results);
    }
}
