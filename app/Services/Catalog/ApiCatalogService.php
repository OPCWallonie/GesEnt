<?php

namespace App\Services\Catalog;

use App\Models\CatalogConfig;
use App\Models\CatalogProduit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de synchronisation via API HTTP.
 *
 * Desco : accès via leur portail B2B (desco.be)
 *   - Auth : POST /api/login avec identifiant/mot_de_passe
 *   - Catalog : GET /api/products?page=X
 *   - Format réponse : JSON
 *
 * VanMarke : portail professional (vanmarke.be)
 *   - Auth : POST /auth/token
 *   - Catalog : GET /catalog/products
 *
 * NOTE : Renseignez vos identifiants B2B dans Paramètres > Catalogues.
 * Les URLs et formats exacts dépendent de l'accord commercial avec chaque fournisseur.
 * Ce service est conçu pour être adapté selon la documentation API reçue.
 */
class ApiCatalogService
{
    /**
     * Synchroniser le catalogue Desco via l'API B2B.
     * Configurez vos identifiants dans la section Catalogues des paramètres.
     */
    public function syncDesco(CatalogConfig $config): array
    {
        if (!$config->url_api || !$config->identifiant) {
            return ['erreur' => 'Identifiants Desco non configurés. Renseignez-les dans Paramètres > Catalogues.'];
        }

        try {
            // 1. Authentification
            $authResponse = Http::timeout(30)
                ->post($config->url_api . '/auth/login', [
                    'username' => $config->identifiant,
                    'password' => $config->mot_de_passe_decrypte,
                    'client'   => $config->numero_client,
                ]);

            if (!$authResponse->successful()) {
                return ['erreur' => "Authentification Desco échouée : " . $authResponse->status()];
            }

            $token    = $authResponse->json('access_token') ?? $authResponse->json('token');
            $inseres  = 0;
            $mis_a_jour = 0;
            $page     = 1;

            // 2. Récupération paginée du catalogue
            do {
                $response = Http::withToken($token)
                    ->timeout(60)
                    ->get($config->url_api . '/catalog/products', [
                        'page'     => $page,
                        'per_page' => 500,
                    ]);

                if (!$response->successful()) break;

                $data    = $response->json();
                $produits = $data['data'] ?? $data['products'] ?? $data ?? [];

                foreach ($produits as $p) {
                    $this->upsertProduit('desco', $p, $config->marge_defaut, [
                        'reference'   => $p['artikelnummer'] ?? $p['code'] ?? $p['reference'] ?? null,
                        'designation' => $p['omschrijving'] ?? $p['description'] ?? $p['designation'] ?? null,
                        'prix'        => $p['nettoprijs'] ?? $p['prijs'] ?? $p['price'] ?? 0,
                        'tva'         => $p['btw'] ?? $p['tva'] ?? 21,
                        'unite'       => $p['eenheid'] ?? $p['unite'] ?? 'pièce',
                        'categorie'   => $p['categorie'] ?? $p['groep'] ?? null,
                        'marque'      => $p['merk'] ?? $p['brand'] ?? null,
                        'ean'         => $p['ean'] ?? null,
                        'stock'       => $p['voorraad'] ?? $p['stock'] ?? true,
                    ], $inseres, $mis_a_jour);
                }

                $page++;
                $hasMore = isset($data['next_page_url']) && $data['next_page_url'];
            } while ($hasMore && $page <= 200);

            $this->majConfig($config, $inseres + $mis_a_jour);
            return compact('inseres', 'mis_a_jour');

        } catch (\Exception $e) {
            Log::error('Desco API sync failed', ['error' => $e->getMessage()]);
            return ['erreur' => 'Erreur API Desco : ' . $e->getMessage()];
        }
    }

    /**
     * Synchroniser le catalogue VanMarke via l'API B2B.
     */
    public function syncVanMarke(CatalogConfig $config): array
    {
        if (!$config->url_api || !$config->identifiant) {
            return ['erreur' => 'Identifiants VanMarke non configurés. Renseignez-les dans Paramètres > Catalogues.'];
        }

        try {
            // Authentification OAuth2 / Basic selon l'accord VanMarke
            $authResponse = Http::timeout(30)
                ->asForm()
                ->post($config->url_api . '/oauth/token', [
                    'grant_type'    => 'password',
                    'username'      => $config->identifiant,
                    'password'      => $config->mot_de_passe_decrypte,
                    'client_number' => $config->numero_client,
                ]);

            if (!$authResponse->successful()) {
                return ['erreur' => "Authentification VanMarke échouée : " . $authResponse->status()];
            }

            $token    = $authResponse->json('access_token');
            $inseres  = 0;
            $mis_a_jour = 0;
            $page     = 1;

            do {
                $response = Http::withToken($token)
                    ->timeout(60)
                    ->get($config->url_api . '/products', [
                        'page'  => $page,
                        'limit' => 500,
                    ]);

                if (!$response->successful()) break;

                $data    = $response->json();
                $produits = $data['items'] ?? $data['products'] ?? $data ?? [];

                foreach ($produits as $p) {
                    $this->upsertProduit('vanmarke', $p, $config->marge_defaut, [
                        'reference'   => $p['code'] ?? $p['reference'] ?? null,
                        'designation' => $p['designation'] ?? $p['libelle'] ?? $p['description'] ?? null,
                        'prix'        => $p['prix_ht'] ?? $p['prix'] ?? $p['tarif'] ?? 0,
                        'tva'         => $p['tva'] ?? 21,
                        'unite'       => $p['unite'] ?? $p['unit'] ?? 'pièce',
                        'categorie'   => $p['famille'] ?? $p['categorie'] ?? null,
                        'marque'      => $p['marque'] ?? $p['brand'] ?? null,
                        'ean'         => $p['ean'] ?? null,
                        'stock'       => $p['stock'] ?? $p['disponible'] ?? true,
                    ], $inseres, $mis_a_jour);
                }

                $page++;
                $hasMore = !empty($data['next']) || (isset($data['total']) && $page * 500 < $data['total']);
            } while ($hasMore && $page <= 200);

            $this->majConfig($config, $inseres + $mis_a_jour);
            return compact('inseres', 'mis_a_jour');

        } catch (\Exception $e) {
            Log::error('VanMarke API sync failed', ['error' => $e->getMessage()]);
            return ['erreur' => 'Erreur API VanMarke : ' . $e->getMessage()];
        }
    }

    /**
     * Adaptateur générique pour tout fournisseur avec une API REST JSON.
     * Tente une auth par token puis une pagination GET /products ou /catalog/products.
     * Les champs sont mappés via les clés les plus communes (FR + NL).
     */
    public function syncGenerique(CatalogConfig $config): array
    {
        if (!$config->url_api || !$config->identifiant) {
            return ['erreur' => "Identifiants non configurés pour {$config->nom_affichage}."];
        }

        try {
            // Tentative d'auth (Bearer token)
            $token = null;
            if ($config->mot_de_passe_decrypte) {
                $authResponse = Http::timeout(30)->post($config->url_api . '/auth/login', [
                    'username' => $config->identifiant,
                    'password' => $config->mot_de_passe_decrypte,
                    'client'   => $config->numero_client,
                ]);
                if ($authResponse->successful()) {
                    $token = $authResponse->json('access_token') ?? $authResponse->json('token');
                }
            }

            $http     = $token ? Http::withToken($token)->timeout(60) : Http::withBasicAuth($config->identifiant, $config->mot_de_passe_decrypte ?? '')->timeout(60);
            $inseres  = 0;
            $mis_a_jour = 0;
            $page     = 1;

            // Essai sur /catalog/products puis /products
            $endpoints = ['/catalog/products', '/products', '/articles', '/tarieven'];

            foreach ($endpoints as $endpoint) {
                $test = $http->get($config->url_api . $endpoint, ['page' => 1, 'per_page' => 10]);
                if ($test->successful()) {
                    do {
                        $response = $http->get($config->url_api . $endpoint, ['page' => $page, 'per_page' => 500, 'limit' => 500]);
                        if (!$response->successful()) break;

                        $data    = $response->json();
                        $produits = $data['data'] ?? $data['items'] ?? $data['products'] ?? $data ?? [];
                        if (!is_array($produits) || empty($produits)) break;

                        foreach ($produits as $p) {
                            $this->upsertProduit($config->fournisseur, $p, $config->marge_defaut, [
                                'reference'   => $p['code'] ?? $p['reference'] ?? $p['artikelnummer'] ?? $p['ref'] ?? null,
                                'designation' => $p['designation'] ?? $p['description'] ?? $p['omschrijving'] ?? $p['libelle'] ?? null,
                                'prix'        => $p['prix_ht'] ?? $p['prix'] ?? $p['price'] ?? $p['nettoprijs'] ?? $p['prijs'] ?? 0,
                                'tva'         => $p['tva'] ?? $p['btw'] ?? $p['vat'] ?? 21,
                                'unite'       => $p['unite'] ?? $p['unit'] ?? $p['eenheid'] ?? 'pièce',
                                'categorie'   => $p['categorie'] ?? $p['famille'] ?? $p['groep'] ?? null,
                                'marque'      => $p['marque'] ?? $p['brand'] ?? $p['merk'] ?? null,
                                'ean'         => $p['ean'] ?? $p['gtin'] ?? null,
                                'stock'       => $p['stock'] ?? $p['disponible'] ?? $p['voorraad'] ?? true,
                            ], $inseres, $mis_a_jour);
                        }

                        $page++;
                        $hasMore = isset($data['next_page_url']) && $data['next_page_url']
                                || (!empty($data['total']) && $page * 500 < $data['total']);
                    } while ($hasMore && $page <= 200);

                    $this->majConfig($config, $inseres + $mis_a_jour);
                    return compact('inseres', 'mis_a_jour');
                }
            }

            return ['erreur' => "Aucun endpoint API reconnu sur {$config->url_api}. Vérifiez la documentation de votre fournisseur."];

        } catch (\Exception $e) {
            Log::error('API générique sync failed', ['fournisseur' => $config->fournisseur, 'error' => $e->getMessage()]);
            return ['erreur' => "Erreur API {$config->nom_affichage} : " . $e->getMessage()];
        }
    }

    // ------------------------------------------------------------------

    private function upsertProduit(string $fournisseur, array $raw, float $marge, array $mapped, int &$inseres, int &$mis_a_jour): void
    {
        if (!$mapped['reference'] || !$mapped['designation']) return;

        $prixCatalogue = (float) str_replace(',', '.', $mapped['prix']);
        $prixRevente   = $marge > 0 ? round($prixCatalogue * (1 + $marge / 100), 4) : $prixCatalogue;

        $produitExistant = CatalogProduit::where('fournisseur', $fournisseur)
            ->where('reference', $mapped['reference'])
            ->select('id', 'fournisseur', 'reference', 'prix_catalogue')
            ->first();

        app(PrixHistoriqueService::class)->enregistrerSiChange($produitExistant, $prixCatalogue, 'api');

        $produit = CatalogProduit::updateOrCreate(
            ['fournisseur' => $fournisseur, 'reference' => $mapped['reference']],
            [
                'designation'    => $mapped['designation'],
                'prix_catalogue' => $prixCatalogue,
                'prix_revente'   => $prixRevente,
                'taux_tva'       => in_array((int)$mapped['tva'], [0,6,12,21]) ? $mapped['tva'] : 21,
                'unite'          => $mapped['unite'],
                'categorie'      => $mapped['categorie'],
                'marque'         => $mapped['marque'],
                'ean'            => $mapped['ean'],
                'en_stock'       => (bool)$mapped['stock'],
                'donnees_brutes' => $raw,
                'derniere_sync'  => now(),
            ]
        );

        $produit->wasRecentlyCreated ? $inseres++ : $mis_a_jour++;
    }

    private function majConfig(CatalogConfig $config, int $total): void
    {
        $config->update([
            'derniere_sync' => now(),
            'nb_produits'   => CatalogProduit::where('fournisseur', $config->fournisseur)->count(),
        ]);
    }
}
