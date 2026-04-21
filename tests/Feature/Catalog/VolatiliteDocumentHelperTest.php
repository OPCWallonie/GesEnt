<?php

namespace Tests\Feature\Catalog;

use App\Models\CatalogProduit;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDocument;
use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\VolatiliteDocumentHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolatiliteDocumentHelperTest extends TestCase
{
    use RefreshDatabase;

    private VolatiliteDocumentHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = app(VolatiliteDocumentHelper::class);
        ParametresEntreprise::firstOrCreate(['id' => 1], [
            'nom'                              => 'Test',
            'volatilite_active'                => true,
            'volatilite_seuil_ligne_devis_eur' => 100.0,
        ]);
    }

    public function test_retourne_vides_si_module_inactif(): void
    {
        ParametresEntreprise::instance()->update(['volatilite_active' => false]);
        $devis = $this->creerDevisAvecLigne('b', 200.0);

        $data = $this->helper->preparerPourDocument($devis);

        $this->assertEmpty($data['badgesParProduit']);
        $this->assertEmpty($data['alternativesParProduit']);
    }

    public function test_retourne_badge_pour_ligne_pertinente(): void
    {
        $devis = $this->creerDevisAvecLigne('b', 200.0, signalAbsolu: true);

        $data = $this->helper->preparerPourDocument($devis);

        $this->assertNotEmpty($data['badgesParProduit']);
    }

    public function test_ne_retourne_pas_badge_si_montant_sous_seuil(): void
    {
        $devis = $this->creerDevisAvecLigne('b', 50.0, signalAbsolu: true);

        $data = $this->helper->preparerPourDocument($devis);

        $this->assertEmpty($data['badgesParProduit']);
    }

    public function test_ne_retourne_pas_badge_pour_classe_stable(): void
    {
        $devis = $this->creerDevisAvecLigne('stable', 300.0, signalAbsolu: false);

        $data = $this->helper->preparerPourDocument($devis);

        $this->assertEmpty($data['badgesParProduit']);
    }

    public function test_alternatives_retournees_meme_si_produit_stable(): void
    {
        $ean = 'EAN-' . uniqid();

        // Produit stable sur le devis
        $produitRef = CatalogProduit::create([
            'fournisseur'               => 'vanmarke',
            'reference'                 => 'STABLE-' . uniqid(),
            'designation'               => 'Produit stable',
            'prix_catalogue'            => 100.00,
            'prix_revente'              => 100.00,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'ean'                       => $ean,
            'volatilite_classe'         => 'stable',
            'volatilite_tendance_pct'   => 0.0,
            'volatilite_calculee_at'    => now(),
        ]);

        // Alternative moins chère avec même EAN (signal prix déclenché)
        CatalogProduit::create([
            'fournisseur'               => 'altra',
            'reference'                 => 'ALT-' . uniqid(),
            'designation'               => 'Produit alternatif',
            'prix_catalogue'            => 80.00,
            'prix_revente'              => 80.00,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'ean'                       => $ean,
            'volatilite_classe'         => 'b',
            'volatilite_tendance_pct'   => -5.0,
            'volatilite_calculee_at'    => now(),
        ]);

        $client = Client::create(['nom' => 'Client alt', 'actif' => true]);
        $devis  = Devis::create([
            'numero'        => 'DEV-' . uniqid(),
            'client_id'     => $client->id,
            'statut'        => 'brouillon',
            'date_document' => now(),
        ]);

        LigneDocument::create([
            'documentable_type'  => Devis::class,
            'documentable_id'    => $devis->id,
            'catalog_produit_id' => $produitRef->id,
            'ordre'              => 1,
            'est_section'        => false,
            'designation'        => $produitRef->designation,
            'unite'              => $produitRef->unite,
            'quantite'           => 2,
            'prix_unitaire'      => 100.00,
            'remise_valeur'      => 0,
            'remise_type'        => 'montant',
            'taux_tva'           => 21,
            'montant_ht'         => 200.00,
        ]);

        $data = $this->helper->preparerPourDocument($devis);

        // Badge absent car produit stable
        $this->assertEmpty($data['badgesParProduit']);
        // Alternatives présentes malgré l'absence de badge
        $this->assertNotEmpty($data['alternativesParProduit']);
    }

    // ── Helpers ──

    private function creerDevisAvecLigne(
        string $classe,
        float  $montantHt,
        bool   $signalAbsolu  = false,
        bool   $signalRelatif = false,
    ): Devis {
        $produit = CatalogProduit::create([
            'fournisseur'               => 'vanmarke',
            'reference'                 => 'DOC-' . uniqid(),
            'designation'               => 'Produit doc test',
            'prix_catalogue'            => $montantHt,
            'prix_revente'              => $montantHt,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'volatilite_classe'         => $classe,
            'volatilite_tendance_pct'   => 10.0,
            'volatilite_amplitude_pct'  => 15.0,
            'volatilite_signal_absolu'  => $signalAbsolu,
            'volatilite_signal_relatif' => $signalRelatif,
            'volatilite_calculee_at'    => now(),
        ]);

        $client = Client::create(['nom' => 'Client test', 'actif' => true]);
        $devis = Devis::create([
            'numero'        => 'DEV-' . uniqid(),
            'client_id'     => $client->id,
            'statut'        => 'brouillon',
            'date_document' => now(),
        ]);

        LigneDocument::create([
            'documentable_type'  => Devis::class,
            'documentable_id'    => $devis->id,
            'catalog_produit_id' => $produit->id,
            'ordre'              => 1,
            'est_section'        => false,
            'designation'        => $produit->designation,
            'unite'              => $produit->unite,
            'quantite'           => 1,
            'prix_unitaire'      => $montantHt,
            'remise_valeur'      => 0,
            'remise_type'        => 'montant',
            'taux_tva'           => 21,
            'montant_ht'         => $montantHt,
        ]);

        return $devis;
    }
}
