<?php

namespace Tests\Feature\Catalog;

use App\Models\CatalogProduit;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDocument;
use App\Models\ParametresEntreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SwapFournisseurTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private const EAN = '4011200055555';

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        ParametresEntreprise::firstOrCreate(['id' => 1], ['nom' => 'Test']);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_swap_met_a_jour_la_ligne(): void
    {
        [$produit1, $produit2, $ligne] = $this->creerContexteSwap(tva1: 21, tva2: 21);

        $response = $this->actingAs($this->user)
            ->postJson(route('lignes-document.swap-fournisseur', $ligne), [
                'catalog_produit_id' => $produit2->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('avertissement', null);

        $ligne->refresh();
        $this->assertSame($produit2->id, $ligne->catalog_produit_id);
        $this->assertSame($produit2->designation, $ligne->designation);
    }

    public function test_swap_retourne_avertissement_si_tva_change(): void
    {
        [$produit1, $produit2, $ligne] = $this->creerContexteSwap(tva1: 21, tva2: 6);

        $response = $this->actingAs($this->user)
            ->postJson(route('lignes-document.swap-fournisseur', $ligne), [
                'catalog_produit_id' => $produit2->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($response->json('avertissement'));
        $this->assertStringContainsString('TVA', $response->json('avertissement'));
    }

    public function test_swap_refuse_si_ean_different(): void
    {
        $p1 = $this->creerProduit('vanmarke', ean: '1111111111111');
        $p2 = $this->creerProduit('wasco',   ean: '2222222222222');
        $ligne = $this->creerLigne($p1);

        $response = $this->actingAs($this->user)
            ->postJson(route('lignes-document.swap-fournisseur', $ligne), [
                'catalog_produit_id' => $p2->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_swap_refuse_si_produit_inexistant(): void
    {
        $p1 = $this->creerProduit('vanmarke');
        $ligne = $this->creerLigne($p1);

        $response = $this->actingAs($this->user)
            ->postJson(route('lignes-document.swap-fournisseur', $ligne), [
                'catalog_produit_id' => 99999,
            ]);

        $response->assertStatus(422);
    }

    public function test_swap_recalcule_montant_ht(): void
    {
        [$p1, $p2, $ligne] = $this->creerContexteSwap(prix1: 10.0, prix2: 8.0);
        // Ligne : qte=3, prix=10, remise=0 → montant=30
        $ligne->update(['quantite' => 3, 'prix_unitaire' => 10, 'montant_ht' => 30]);

        $this->actingAs($this->user)
            ->postJson(route('lignes-document.swap-fournisseur', $ligne), [
                'catalog_produit_id' => $p2->id,
            ]);

        $ligne->refresh();
        $this->assertEqualsWithDelta(24.0, (float) $ligne->montant_ht, 0.01);
    }

    // ── Helpers ──

    private function creerContexteSwap(
        float $prix1 = 10.0, float $prix2 = 9.0,
        float $tva1  = 21,   float $tva2  = 21,
    ): array {
        $p1    = $this->creerProduit('vanmarke', prix: $prix1, tva: $tva1);
        $p2    = $this->creerProduit('wasco',    prix: $prix2, tva: $tva2);
        $ligne = $this->creerLigne($p1);
        return [$p1, $p2, $ligne];
    }

    private function creerProduit(
        string $fournisseur,
        float  $prix  = 10.0,
        float  $tva   = 21,
        string $ean   = self::EAN,
    ): CatalogProduit {
        return CatalogProduit::create([
            'fournisseur'    => $fournisseur,
            'reference'      => strtoupper($fournisseur) . '-' . uniqid(),
            'designation'    => "Produit {$fournisseur}",
            'prix_catalogue' => $prix,
            'prix_revente'   => $prix,
            'taux_tva'       => $tva,
            'unite'          => 'pièce',
            'ean'            => $ean,
        ]);
    }

    private function creerLigne(CatalogProduit $produit): LigneDocument
    {
        $client = Client::create(['nom' => 'Client test', 'actif' => true]);
        $devis = Devis::create([
            'numero'         => 'DEV-TEST-' . uniqid(),
            'client_id'      => $client->id,
            'statut'         => 'brouillon',
            'date_document'  => now(),
        ]);

        return LigneDocument::create([
            'documentable_type'  => Devis::class,
            'documentable_id'    => $devis->id,
            'catalog_produit_id' => $produit->id,
            'ordre'              => 1,
            'est_section'        => false,
            'designation'        => $produit->designation,
            'unite'              => $produit->unite,
            'quantite'           => 1,
            'prix_unitaire'      => $produit->prix_revente,
            'remise_valeur'      => 0,
            'remise_type'        => 'montant',
            'taux_tva'           => $produit->taux_tva,
            'montant_ht'         => (float) $produit->prix_revente,
        ]);
    }
}
