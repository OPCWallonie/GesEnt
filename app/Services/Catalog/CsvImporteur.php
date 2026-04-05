<?php

namespace App\Services\Catalog;

use App\Models\CatalogConfig;
use App\Models\CatalogProduit;
use Illuminate\Http\UploadedFile;

/**
 * Importeur CSV générique pour les catalogues fournisseurs.
 *
 * Chaque fournisseur a son propre mapping de colonnes.
 * Le CSV peut être encodé en UTF-8 ou Windows-1252 (latin1).
 */
class CsvImporteur
{
    // ---------------------------------------------------------------
    // Mappings par fournisseur
    // Clé = champ interne, valeur = nom(s) de colonne CSV possible(s)
    // ---------------------------------------------------------------
    private const MAPPINGS = [
        'desco' => [
            'reference'        => ['artikelnummer', 'article', 'code', 'ref', 'artnr', 'artikelcode'],
            'designation'      => ['omschrijving', 'description', 'designation', 'libelle', 'artikel'],
            'prix_catalogue'   => ['nettoprijs', 'prix_ht', 'prix', 'price', 'prijs', 'netto'],
            'taux_tva'         => ['btw', 'tva', 'vat', 'btw%'],
            'unite'            => ['eenheid', 'unite', 'unit', 'vpe'],
            'categorie'        => ['categorie', 'categorie1', 'groep', 'famille', 'category'],
            'sous_categorie'   => ['ondercategorie', 'sous_categorie', 'subgroep'],
            'marque'           => ['merk', 'marque', 'brand', 'fabrikant'],
            'ean'              => ['ean', 'barcode', 'gtin'],
            'en_stock'         => ['voorraad', 'stock', 'disponible', 'beschikbaar'],
            'quantite_stock'   => ['voorraad_stuks', 'qty_stock', 'quantite'],
            'delai_livraison'  => ['levertijd', 'delai', 'delivery_time'],
        ],
        'vanmarke' => [
            'reference'        => ['code', 'ref', 'reference', 'artikelcode', 'article'],
            'designation'      => ['designation', 'libelle', 'omschrijving', 'description', 'artikel'],
            'prix_catalogue'   => ['prix_ht', 'prix', 'netto', 'prijs', 'price', 'tarif'],
            'taux_tva'         => ['tva', 'btw', 'vat'],
            'unite'            => ['unite', 'unit', 'eenheid'],
            'categorie'        => ['famille', 'categorie', 'groep', 'category'],
            'sous_categorie'   => ['sous_famille', 'sous_categorie', 'subgroep'],
            'marque'           => ['marque', 'brand', 'merk'],
            'ean'              => ['ean', 'gtin', 'barcode'],
            'en_stock'         => ['stock', 'disponible', 'voorraad'],
            'quantite_stock'   => ['qte_stock', 'qty', 'quantite'],
            'delai_livraison'  => ['delai', 'livraison', 'levertijd'],
        ],
        'wasco' => [
            'reference'      => ['artikelno', 'code', 'ref', 'reference'],
            'designation'    => ['omschrijving', 'designation', 'description'],
            'prix_catalogue' => ['prijs', 'prix_ht', 'price'],
            'taux_tva'       => ['btw', 'tva'],
            'unite'          => ['eenheid', 'unite'],
            'categorie'      => ['categorie', 'groep'],
            'marque'         => ['merk', 'marque'],
            'ean'            => ['ean'],
            'en_stock'       => ['voorraad', 'stock'],
        ],
        'autre' => [
            'reference'      => ['reference', 'ref', 'code', 'article', 'artikelnummer'],
            'designation'    => ['designation', 'description', 'libelle', 'omschrijving'],
            'prix_catalogue' => ['prix_ht', 'prix', 'price', 'netto', 'prijs', 'tarif'],
            'taux_tva'       => ['tva', 'btw', 'vat'],
            'unite'          => ['unite', 'unit', 'eenheid'],
            'categorie'      => ['categorie', 'famille', 'groep', 'category'],
            'marque'         => ['marque', 'brand', 'merk'],
            'ean'            => ['ean', 'barcode'],
            'en_stock'       => ['stock', 'disponible'],
        ],
    ];

    public function importer(string $fournisseur, string $cheminFichier, float $margePct = 0): array
    {
        $mapping = self::MAPPINGS[$fournisseur] ?? self::MAPPINGS['autre'];

        $handle = fopen($cheminFichier, 'r');
        $this->skipBom($handle);

        $separateur = $this->detecterSeparateur($cheminFichier);
        $entetes     = null;
        $colMap      = [];
        $inseres     = 0;
        $mis_a_jour  = 0;
        $ignores     = 0;
        $erreurs     = [];

        while (($ligne = fgetcsv($handle, 0, $separateur)) !== false) {
            // Nettoyer l'encodage Windows-1252 éventuel
            $ligne = array_map(fn($v) => mb_convert_encoding($v, 'UTF-8', 'UTF-8,ISO-8859-1'), $ligne);
            $ligne = array_map('trim', $ligne);

            // Première ligne non vide = entêtes
            if ($entetes === null) {
                $entetes = array_map('strtolower', $ligne);
                $colMap  = $this->construireMapping($entetes, $mapping);
                continue;
            }

            if (count($ligne) < 2 || array_filter($ligne) === []) continue;

            $row = array_combine(
                array_slice($entetes, 0, count($ligne)),
                $ligne
            );

            $reference   = $this->extraire($row, $colMap, 'reference');
            $designation = $this->extraire($row, $colMap, 'designation');

            if (!$reference || !$designation) { $ignores++; continue; }

            $prixCatalogue = (float) str_replace([',', ' '], ['.', ''], $this->extraire($row, $colMap, 'prix_catalogue', '0'));
            $tva           = (float) str_replace(',', '.', $this->extraire($row, $colMap, 'taux_tva', '21'));
            $enStock       = $this->boolStock($this->extraire($row, $colMap, 'en_stock', '1'));

            $prixRevente = $margePct > 0
                ? round($prixCatalogue * (1 + $margePct / 100), 4)
                : $prixCatalogue;

            try {
                $produit = CatalogProduit::updateOrCreate(
                    ['fournisseur' => $fournisseur, 'reference' => $reference],
                    [
                        'designation'     => $designation,
                        'description'     => $this->extraire($row, $colMap, 'description'),
                        'unite'           => $this->extraire($row, $colMap, 'unite', 'pièce'),
                        'prix_catalogue'  => $prixCatalogue,
                        'prix_revente'    => $prixRevente,
                        'taux_tva'        => in_array((int)$tva, [0,6,12,21]) ? $tva : 21,
                        'categorie'       => $this->extraire($row, $colMap, 'categorie'),
                        'sous_categorie'  => $this->extraire($row, $colMap, 'sous_categorie'),
                        'marque'          => $this->extraire($row, $colMap, 'marque'),
                        'ean'             => $this->extraire($row, $colMap, 'ean'),
                        'en_stock'        => $enStock,
                        'quantite_stock'  => (int)($this->extraire($row, $colMap, 'quantite_stock') ?: 0) ?: null,
                        'delai_livraison' => $this->extraire($row, $colMap, 'delai_livraison'),
                        'donnees_brutes'  => $row,
                        'derniere_sync'   => now(),
                    ]
                );

                $produit->wasRecentlyCreated ? $inseres++ : $mis_a_jour++;
            } catch (\Exception $e) {
                $erreurs[] = "Réf {$reference} : " . $e->getMessage();
                $ignores++;
            }
        }

        fclose($handle);

        // Mettre à jour les stats du fournisseur
        CatalogConfig::updateOrCreate(['fournisseur' => $fournisseur], [
            'nom_affichage' => CatalogProduit::FOURNISSEURS[$fournisseur] ?? ucfirst($fournisseur),
            'derniere_sync' => now(),
            'nb_produits'   => CatalogProduit::where('fournisseur', $fournisseur)->count(),
        ]);

        return compact('inseres', 'mis_a_jour', 'ignores', 'erreurs');
    }

    // ------------------------------------------------------------------

    private function construireMapping(array $entetes, array $mapping): array
    {
        $colMap = [];
        foreach ($mapping as $champ => $aliases) {
            foreach ($entetes as $i => $header) {
                if (in_array($header, $aliases)) {
                    $colMap[$champ] = $i;
                    break;
                }
            }
        }
        return $colMap;
    }

    private function extraire(array $row, array $colMap, string $champ, string $defaut = ''): string
    {
        if (!isset($colMap[$champ])) return $defaut;
        $entetes = array_keys($row);
        $idx     = $colMap[$champ];
        $valeurs = array_values($row);
        return $valeurs[$idx] ?? $defaut;
    }

    private function boolStock(string $val): bool
    {
        $v = strtolower(trim($val));
        if (in_array($v, ['0', 'nee', 'non', 'false', 'no', 'out', 'épuisé'])) return false;
        return true;
    }

    private function detecterSeparateur(string $path): string
    {
        $ligne = fgets(fopen($path, 'r'));
        return (substr_count($ligne, ';') >= substr_count($ligne, ',')) ? ';' : ',';
    }

    private function skipBom($handle): void
    {
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
    }
}
