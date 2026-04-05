<?php

namespace App\Http\Controllers;

use App\Models\CatalogConfig;
use App\Models\CatalogProduit;
use App\Services\Catalog\ApiCatalogService;
use App\Services\Catalog\CsvImporteur;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $q           = $request->get('q', '');
        $fournisseur = $request->get('fournisseur');
        $categorie   = $request->get('categorie');
        $enStock     = $request->boolean('en_stock', false);

        $produits = CatalogProduit::query()
            ->when(strlen($q) >= 2, fn($query) => $query->search($q))
            ->when($fournisseur, fn($query) => $query->where('fournisseur', $fournisseur))
            ->when($categorie, fn($query) => $query->where('categorie', $categorie))
            ->when($enStock, fn($query) => $query->where('en_stock', true))
            ->orderBy('fournisseur')
            ->orderBy('designation')
            ->paginate(30)
            ->withQueryString();

        $fournisseurs = CatalogProduit::select('fournisseur')
            ->distinct()->orderBy('fournisseur')
            ->pluck('fournisseur');

        $categories = CatalogProduit::select('categorie')
            ->when($fournisseur, fn($query) => $query->where('fournisseur', $fournisseur))
            ->whereNotNull('categorie')
            ->distinct()->orderBy('categorie')
            ->pluck('categorie');

        $configs = CatalogConfig::orderBy('nom_affichage')->get();

        $totalProduits = CatalogProduit::count();

        return view('catalog.index', compact(
            'produits', 'fournisseurs', 'categories', 'configs',
            'totalProduits', 'q', 'fournisseur', 'categorie', 'enStock'
        ));
    }

    /**
     * Recherche AJAX pour l'autocomplétion dans les lignes de devis/BDC.
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) return response()->json([]);

        $fournisseur = $request->get('fournisseur');

        $produits = CatalogProduit::search($q)
            ->when($fournisseur, fn($query) => $query->where('fournisseur', $fournisseur))
            ->orderByRaw('en_stock DESC')
            ->limit(15)
            ->get(['id', 'fournisseur', 'reference', 'designation', 'unite', 'prix_revente', 'prix_catalogue', 'taux_tva', 'en_stock', 'marque']);

        return response()->json($produits->map(fn($p) => [
            'id'          => $p->id,
            'fournisseur' => $p->nom_fournisseur,
            'reference'   => $p->reference,
            'designation' => $p->designation . ($p->marque ? " ({$p->marque})" : ''),
            'unite'       => $p->unite,
            'prix'        => (float) $p->prix_revente,
            'prix_base'   => (float) $p->prix_catalogue,
            'taux_tva'    => (float) $p->taux_tva,
            'en_stock'    => $p->en_stock,
        ]));
    }

    /**
     * Import CSV d'un fournisseur.
     */
    public function import(Request $request, CsvImporteur $importeur)
    {
        $data = $request->validate([
            'fournisseur' => 'required|string|max:50|regex:/^[a-z0-9_-]+$/',
            'fichier'     => 'required|file|mimes:csv,txt,xls,xlsx|max:20480',
            'marge'       => 'nullable|numeric|min:0|max:200',
        ]);

        $marge = (float)($data['marge'] ?? 0);

        // Pour Excel : convertir en CSV d'abord si possible
        $fichier = $request->file('fichier');
        $chemin  = $fichier->getPathname();

        // Si c'est un Excel, on ne peut pas le traiter directement sans phpspreadsheet
        // On informe l'utilisateur d'exporter en CSV depuis Excel
        if (in_array($fichier->getClientOriginalExtension(), ['xls', 'xlsx'])) {
            return back()->with('error', 'Les fichiers Excel ne sont pas supportés directement. Ouvrez le fichier dans Excel et exportez-le en CSV (séparateur ; ou ,) avant de l\'importer.');
        }

        $resultat = $importeur->importer($data['fournisseur'], $chemin, $marge);

        $msg = "{$resultat['inseres']} produits importés, {$resultat['mis_a_jour']} mis à jour";
        if ($resultat['ignores'] > 0) $msg .= ", {$resultat['ignores']} ignorés";
        if (!empty($resultat['erreurs'])) {
            $msg .= '. Erreurs : ' . implode(' | ', array_slice($resultat['erreurs'], 0, 3));
        }

        return redirect()->route('catalog.index', ['fournisseur' => $data['fournisseur']])
            ->with('success', $msg);
    }

    /**
     * Synchronisation via API (admin seulement).
     */
    public function sync(Request $request, ApiCatalogService $service)
    {
        $fournisseur = $request->input('fournisseur');
        $config      = CatalogConfig::where('fournisseur', $fournisseur)->first();

        if (!$config) {
            return back()->with('error', "Fournisseur {$fournisseur} non configuré.");
        }

        // Adaptateurs connus ; pour les autres, essai avec l'adaptateur générique Desco
        $resultat = match ($fournisseur) {
            'desco'    => $service->syncDesco($config),
            'vanmarke' => $service->syncVanMarke($config),
            default    => $config->url_api
                ? $service->syncGenerique($config)
                : ['erreur' => "Aucune URL API configurée pour {$fournisseur}. Utilisez l'import CSV."],
        };

        if (isset($resultat['erreur'])) {
            return back()->with('error', $resultat['erreur']);
        }

        return back()->with('success', "{$resultat['inseres']} insérés, {$resultat['mis_a_jour']} mis à jour.");
    }

    /**
     * Mise à jour de la config d'un fournisseur (URL API, identifiants, marge).
     */
    public function updateConfig(Request $request)
    {
        $data = $request->validate([
            'fournisseur'    => 'required|string',
            'nom_affichage'  => 'required|string|max:100',
            'actif'          => 'nullable|boolean',
            'url_api'        => 'nullable|url',
            'identifiant'    => 'nullable|string|max:200',
            'mot_de_passe'   => 'nullable|string|max:500',
            'numero_client'  => 'nullable|string|max:50',
            'marge_defaut'   => 'nullable|numeric|min:0|max:200',
            'notes'          => 'nullable|string',
        ]);

        $config = CatalogConfig::firstOrNew(['fournisseur' => $data['fournisseur']]);
        $config->fill([
            'nom_affichage'  => $data['nom_affichage'],
            'actif'          => $request->boolean('actif', true),
            'url_api'        => $data['url_api'] ?? null,
            'identifiant'    => $data['identifiant'] ?? null,
            'numero_client'  => $data['numero_client'] ?? null,
            'marge_defaut'   => $data['marge_defaut'] ?? 0,
            'notes'          => $data['notes'] ?? null,
        ]);

        // Ne mettre à jour le mot de passe que s'il est fourni
        if (!empty($data['mot_de_passe'])) {
            $config->mot_de_passe = $data['mot_de_passe'];
        }

        $config->save();

        return back()->with('success', "Configuration {$config->nom_affichage} enregistrée.");
    }

    /**
     * Supprimer tous les produits d'un fournisseur.
     */
    public function vider(Request $request)
    {
        $fournisseur = $request->input('fournisseur');
        $nb = CatalogProduit::where('fournisseur', $fournisseur)->delete();
        CatalogConfig::where('fournisseur', $fournisseur)->update(['nb_produits' => 0, 'derniere_sync' => null]);

        return back()->with('success', "{$nb} produits du catalogue {$fournisseur} supprimés.");
    }

    /**
     * Supprimer entièrement un fournisseur (config + produits).
     */
    public function deleteConfig(Request $request)
    {
        $fournisseur = $request->input('fournisseur');
        $config = CatalogConfig::where('fournisseur', $fournisseur)->firstOrFail();

        $nbProduits = CatalogProduit::where('fournisseur', $fournisseur)->count();
        if ($nbProduits > 0) {
            CatalogProduit::where('fournisseur', $fournisseur)->delete();
        }

        $nom = $config->nom_affichage;
        $config->delete();

        return back()->with('success', "Fournisseur « {$nom} » supprimé ({$nbProduits} produits).");
    }
}
