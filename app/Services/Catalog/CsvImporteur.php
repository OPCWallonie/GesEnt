<?php

namespace App\Services\Catalog;

use App\Events\CatalogProduitsImportes;
use App\Models\CatalogConfig;
use App\Models\CatalogProduit;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvImporteur
{
    public function __construct(private PrixHistoriqueService $historiqueService) {}

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
        $extension = strtolower(pathinfo($cheminFichier, PATHINFO_EXTENSION));

        return match ($extension) {
            'xlsx', 'xls' => $this->importerExcel($fournisseur, $cheminFichier, $margePct),
            default       => $this->importerCsv($fournisseur, $cheminFichier, $margePct),
        };
    }

    private function importerCsv(string $fournisseur, string $cheminFichier, float $margePct): array
    {
        $handle = fopen($cheminFichier, 'r');
        $this->skipBom($handle);
        $separateur = $this->detecterSeparateur($cheminFichier);

        $headers = null;
        $rows    = [];

        while (($ligne = fgetcsv($handle, 0, $separateur)) !== false) {
            $ligne = array_map(fn($v) => mb_convert_encoding($v, 'UTF-8', 'UTF-8,ISO-8859-1'), $ligne);
            $ligne = array_map('trim', $ligne);

            if ($headers === null) {
                $headers = array_map('strtolower', $ligne);
                continue;
            }
            if (count($ligne) < 2 || array_filter($ligne) === []) continue;

            $rows[] = $ligne;
        }
        fclose($handle);

        return $this->importerDepuisRows($fournisseur, $headers ?? [], $rows, $margePct);
    }

    private function importerExcel(string $fournisseur, string $cheminFichier, float $margePct): array
    {
        try {
            $reader = IOFactory::createReaderForFile($cheminFichier);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($cheminFichier);
            $sheet       = $spreadsheet->getActiveSheet();

            $headers = null;
            $rows    = [];

            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $ligne = [];
                foreach ($cellIterator as $cell) {
                    $value   = $cell->getValue();
                    $ligne[] = is_null($value) ? '' : trim((string) $value);
                }

                if ($headers === null) {
                    $headers = array_map('strtolower', $ligne);
                    continue;
                }
                if (count(array_filter($ligne)) === 0) continue;

                $rows[] = $ligne;
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);

            return $this->importerDepuisRows($fournisseur, $headers ?? [], $rows, $margePct);

        } catch (\Exception $e) {
            return [
                'inseres'    => 0,
                'mis_a_jour' => 0,
                'ignores'    => 0,
                'erreurs'    => ["Erreur lecture Excel : " . $e->getMessage()],
            ];
        }
    }

    private function importerDepuisRows(string $fournisseur, array $headers, array $rows, float $margePct): array
    {
        $mapping    = self::MAPPINGS[$fournisseur] ?? self::MAPPINGS['autre'];
        $colMap     = $this->construireMapping($headers, $mapping);
        $inseres    = 0;
        $mis_a_jour = 0;
        $ignores    = 0;
        $erreurs    = [];
        $idsAffectes = [];

        foreach ($rows as $ligneBrute) {
            $row = array_combine(
                array_slice($headers, 0, count($ligneBrute)),
                $ligneBrute
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
                $produitExistant = CatalogProduit::where('fournisseur', $fournisseur)
                    ->where('reference', $reference)
                    ->select('id', 'fournisseur', 'reference', 'prix_catalogue')
                    ->first();

                $this->historiqueService->enregistrerSiChange($produitExistant, $prixCatalogue, 'csv');

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
                $idsAffectes[] = $produit->id;
            } catch (\Exception $e) {
                $erreurs[] = "Réf {$reference} : " . $e->getMessage();
                $ignores++;
            }
        }

        if (!empty($idsAffectes)) {
            event(new CatalogProduitsImportes(
                produitIds: $idsAffectes,
                source: 'csv',
            ));
        }

        CatalogConfig::updateOrCreate(['fournisseur' => $fournisseur], [
            'nom_affichage' => CatalogProduit::FOURNISSEURS[$fournisseur] ?? ucfirst($fournisseur),
            'nb_produits'   => CatalogProduit::where('fournisseur', $fournisseur)->count(),
            'derniere_sync' => now(),
        ]);

        return compact('inseres', 'mis_a_jour', 'ignores', 'erreurs');
    }

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
