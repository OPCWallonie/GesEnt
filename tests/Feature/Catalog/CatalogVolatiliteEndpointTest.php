<?php

namespace Tests\Feature\Catalog;

use App\Models\CatalogProduit;
use App\Models\ParametresEntreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogVolatiliteEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        ParametresEntreprise::firstOrCreate(['id' => 1], [
            'nom'               => 'Test',
            'volatilite_active' => true,
        ]);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_endpoint_volatilite_retourne_badge_et_alternatives(): void
    {
        $produit = CatalogProduit::create([
            'fournisseur'               => 'vanmarke',
            'reference'                 => 'END-001',
            'designation'               => 'Produit endpoint',
            'prix_catalogue'            => 10.00,
            'prix_revente'              => 10.00,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'volatilite_classe'         => 'b',
            'volatilite_tendance_pct'   => 12.0,
            'volatilite_calculee_at'    => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('catalog.volatilite', $produit));

        $response->assertOk()
            ->assertJsonStructure(['badge', 'alternatives'])
            ->assertJsonPath('badge.classe', 'b')
            ->assertJsonPath('badge.niveau', 'warning');
    }

    public function test_search_inclut_volatilite_dans_json(): void
    {
        CatalogProduit::create([
            'fournisseur'               => 'vanmarke',
            'reference'                 => 'SRCH-001',
            'designation'               => 'Tube cuivre 16x18',
            'prix_catalogue'            => 5.00,
            'prix_revente'              => 5.00,
            'taux_tva'                  => 21,
            'unite'                     => 'm',
            'volatilite_classe'         => 'c',
            'volatilite_tendance_pct'   => 20.0,
            'volatilite_amplitude_pct'  => 25.0,
            'volatilite_signal_absolu'  => true,
            'volatilite_calculee_at'    => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('catalog.search', ['q' => 'tube cuivre']));

        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('volatilite', $data[0]);
    }

    public function test_search_volatilite_null_pour_produit_stable(): void
    {
        CatalogProduit::create([
            'fournisseur'               => 'wasco',
            'reference'                 => 'SRCH-002',
            'designation'               => 'Robinet stable 22',
            'prix_catalogue'            => 15.00,
            'prix_revente'              => 15.00,
            'taux_tva'                  => 21,
            'unite'                     => 'pièce',
            'volatilite_classe'         => 'stable',
            'volatilite_calculee_at'    => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('catalog.search', ['q' => 'robinet stable']));

        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertNull($data[0]['volatilite']);
    }
}
