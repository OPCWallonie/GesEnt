<?php

namespace Tests\Feature\Listings;

use App\Models\Client;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FactureListingTest extends TestCase
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

    private function makeFacture(string $statut, ?string $numero = null): Facture
    {
        return Facture::create([
            'numero'             => $numero ?? 'FAC/2026/' . rand(1000, 9999),
            'client_id'          => $this->client->id,
            'statut'             => $statut,
            'date_document'      => now()->toDateString(),
            'montant_ht'         => 0,
            'montant_tva'        => 0,
            'montant_ttc'        => 0,
            'montant_net_a_payer' => 0,
        ]);
    }

    public function test_index_exclut_archives_par_defaut(): void
    {
        $actif   = $this->makeFacture('en_attente', 'FAC/2026/A001');
        $archive = $this->makeFacture('archive', 'FAC/2026/Z001');

        $this->actingAs($this->user)
            ->get(route('factures.index'))
            ->assertStatus(200)
            ->assertSee($actif->numero)
            ->assertDontSee($archive->numero);
    }

    public function test_index_archives_include_affiche_tout(): void
    {
        $actif   = $this->makeFacture('en_attente', 'FAC/2026/A002');
        $archive = $this->makeFacture('archive', 'FAC/2026/Z002');

        $this->actingAs($this->user)
            ->get(route('factures.index', ['archives' => 'include']))
            ->assertStatus(200)
            ->assertSee($actif->numero)
            ->assertSee($archive->numero);
    }

    public function test_index_archives_only_affiche_uniquement_archives(): void
    {
        $actif   = $this->makeFacture('envoyee', 'FAC/2026/A003');
        $archive = $this->makeFacture('archive', 'FAC/2026/Z003');

        $this->actingAs($this->user)
            ->get(route('factures.index', ['archives' => 'only']))
            ->assertStatus(200)
            ->assertDontSee($actif->numero)
            ->assertSee($archive->numero);
    }

    public function test_filtre_statut_combine_avec_archives_include(): void
    {
        $payee   = $this->makeFacture('payee', 'FAC/2026/P001');
        $attente = $this->makeFacture('en_attente', 'FAC/2026/E001');
        $archive = $this->makeFacture('archive', 'FAC/2026/Z004');

        $this->actingAs($this->user)
            ->get(route('factures.index', ['archives' => 'include', 'statut' => 'payee']))
            ->assertStatus(200)
            ->assertSee($payee->numero)
            ->assertDontSee($attente->numero)
            ->assertDontSee($archive->numero);
    }
}
