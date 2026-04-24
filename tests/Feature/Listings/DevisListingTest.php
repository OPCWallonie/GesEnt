<?php

namespace Tests\Feature\Listings;

use App\Models\Client;
use App\Models\Devis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DevisListingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->user   = User::factory()->create();
        $this->user->assignRole('admin');
        $this->client = Client::create(['nom' => 'Client Test', 'actif' => true]);
    }

    private function makeDevis(string $statut, ?string $numero = null): Devis
    {
        return Devis::create([
            'numero'        => $numero ?? 'DEV/2026/' . rand(1000, 9999),
            'client_id'     => $this->client->id,
            'statut'        => $statut,
            'date_document' => now()->toDateString(),
            'montant_ht'    => 0,
            'montant_tva'   => 0,
            'montant_ttc'   => 0,
        ]);
    }

    public function test_index_exclut_archives_par_defaut(): void
    {
        $actif   = $this->makeDevis('en_attente', 'DEV/2026/A001');
        $archive = $this->makeDevis('archive', 'DEV/2026/Z001');

        $this->actingAs($this->user)
            ->get(route('devis.index'))
            ->assertStatus(200)
            ->assertSee($actif->numero)
            ->assertDontSee($archive->numero);
    }

    public function test_index_archives_include_affiche_tout(): void
    {
        $actif   = $this->makeDevis('en_attente', 'DEV/2026/A002');
        $archive = $this->makeDevis('archive', 'DEV/2026/Z002');

        $this->actingAs($this->user)
            ->get(route('devis.index', ['archives' => 'include']))
            ->assertStatus(200)
            ->assertSee($actif->numero)
            ->assertSee($archive->numero);
    }

    public function test_index_archives_only_affiche_uniquement_archives(): void
    {
        $actif   = $this->makeDevis('en_attente', 'DEV/2026/A003');
        $archive = $this->makeDevis('archive', 'DEV/2026/Z003');

        $this->actingAs($this->user)
            ->get(route('devis.index', ['archives' => 'only']))
            ->assertStatus(200)
            ->assertDontSee($actif->numero)
            ->assertSee($archive->numero);
    }

    public function test_filtre_statut_combine_avec_filtre_archives_include(): void
    {
        $valide  = $this->makeDevis('valide', 'DEV/2026/V001');
        $brouillon = $this->makeDevis('brouillon', 'DEV/2026/B001');
        $archive = $this->makeDevis('archive', 'DEV/2026/Z004');

        $this->actingAs($this->user)
            ->get(route('devis.index', ['archives' => 'include', 'statut' => 'valide']))
            ->assertStatus(200)
            ->assertSee($valide->numero)
            ->assertDontSee($brouillon->numero)
            ->assertDontSee($archive->numero);
    }
}
