<?php

namespace Tests\Feature;

use App\Models\CatalogProduit;
use App\Models\CatalogPrixHistorique;
use App\Services\Catalog\CsvImporteur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportExcelTest extends TestCase
{
    use RefreshDatabase;

    private function creerFichierExcel(array $headers, array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $col => $header) {
            $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($coord, $header);
        }
        foreach ($rows as $r => $row) {
            foreach ($row as $c => $value) {
                $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1) . ($r + 2);
                $sheet->setCellValue($coord, $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    public function test_import_xlsx_insere_produits(): void
    {
        $path = $this->creerFichierExcel(
            ['reference', 'designation', 'prix_ht', 'tva', 'unite', 'ean'],
            [
                ['REF001', 'Produit A', '89.50', '21', 'pc', '4005176111111'],
                ['REF002', 'Produit B', '125.00', '21', 'pc', '4005176222222'],
            ]
        );

        $result = app(CsvImporteur::class)->importer('autre', $path, 0);

        $this->assertEquals(2, $result['inseres']);
        $this->assertEquals(0, $result['mis_a_jour']);
        $this->assertEquals(2, CatalogProduit::count());

        $produit = CatalogProduit::where('reference', 'REF001')->first();
        $this->assertEquals('Produit A', $produit->designation);
        $this->assertEqualsWithDelta(89.50, (float) $produit->prix_catalogue, 0.01);
        $this->assertEquals('4005176111111', $produit->ean);

        @unlink($path);
    }

    public function test_import_xlsx_met_a_jour_produits_existants(): void
    {
        CatalogProduit::create([
            'fournisseur'    => 'autre',
            'reference'      => 'REF100',
            'designation'    => 'Ancien nom',
            'prix_catalogue' => 50,
            'prix_revente'   => 50,
            'taux_tva'       => 21,
        ]);

        $path = $this->creerFichierExcel(
            ['reference', 'designation', 'prix_ht', 'tva'],
            [['REF100', 'Nouveau nom', '60', '21']]
        );

        $result = app(CsvImporteur::class)->importer('autre', $path, 0);

        $this->assertEquals(0, $result['inseres']);
        $this->assertEquals(1, $result['mis_a_jour']);

        $produit = CatalogProduit::where('reference', 'REF100')->first();
        $this->assertEquals('Nouveau nom', $produit->designation);
        $this->assertEqualsWithDelta(60.00, (float) $produit->prix_catalogue, 0.01);

        @unlink($path);
    }

    public function test_import_xlsx_applique_marge(): void
    {
        $path = $this->creerFichierExcel(
            ['reference', 'designation', 'prix_ht', 'tva'],
            [['REF200', 'Produit', '100', '21']]
        );

        app(CsvImporteur::class)->importer('autre', $path, 20);

        $produit = CatalogProduit::where('reference', 'REF200')->first();
        $this->assertEqualsWithDelta(100, (float) $produit->prix_catalogue, 0.01);
        $this->assertEqualsWithDelta(120, (float) $produit->prix_revente, 0.01);

        @unlink($path);
    }

    public function test_import_xlsx_cree_historique_prix_sur_changement(): void
    {
        CatalogProduit::create([
            'fournisseur'    => 'autre',
            'reference'      => 'REF-HIST',
            'designation'    => 'Test',
            'prix_catalogue' => 50,
            'prix_revente'   => 50,
            'taux_tva'       => 21,
        ]);

        $path = $this->creerFichierExcel(
            ['reference', 'designation', 'prix_ht', 'tva'],
            [['REF-HIST', 'Test', '55', '21']]
        );

        app(CsvImporteur::class)->importer('autre', $path, 0);

        $this->assertEquals(1, CatalogPrixHistorique::count());
        $hist = CatalogPrixHistorique::first();
        $this->assertEqualsWithDelta(50.00, (float) $hist->prix_avant, 0.01);
        $this->assertEqualsWithDelta(55.00, (float) $hist->prix_apres, 0.01);

        @unlink($path);
    }

    public function test_import_csv_fonctionne_toujours(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'test_csv_') . '.csv';
        file_put_contents($path, "reference;designation;prix_catalogue;tva\nREF-CSV;Produit CSV;42.5;21\n");

        $result = app(CsvImporteur::class)->importer('autre', $path, 0);

        $this->assertEquals(1, $result['inseres']);
        $this->assertEquals('Produit CSV', CatalogProduit::first()->designation);

        @unlink($path);
    }
}
