<?php

namespace Tests\Feature\Listings;

use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FactureAchatListingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Fournisseur $fournisseur;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->user        = User::factory()->create();
        $this->user->assignRole('admin');
        $this->fournisseur = Fournisseur::create(['nom' => 'Fournisseur Test', 'actif' => true]);
    }

    private function makeFactureAchat(string $statut, ?string $numero = null): FactureAchat
    {
        return FactureAchat::create([
            'numero'         => $numero ?? 'ACH/2026/' . rand(1000, 9999),
            'fournisseur_id' => $this->fournisseur->id,
            'statut'         => $statut,
            'date_document'  => now()->toDateString(),
            'montant_ht'     => 0,
            'taux_tva'       => 21,
            'montant_tva'    => 0,
            'montant_ttc'    => 0,
            'categorie'      => 'divers',
        ]);
    }

    public function test_index_exclut_archives_par_defaut(): void
    {
        $actif   = $this->makeFactureAchat('en_attente', 'ACH/2026/A001');
        $archive = $this->makeFactureAchat('archive', 'ACH/2026/Z001');

        $this->actingAs($this->user)
            ->get(route('factures-achat.index'))
            ->assertStatus(200)
            ->assertSee($actif->numero)
            ->assertDontSee($archive->numero);
    }

    public function test_index_archives_include_affiche_tout(): void
    {
        $actif   = $this->makeFactureAchat('en_attente', 'ACH/2026/A002');
        $archive = $this->makeFactureAchat('archive', 'ACH/2026/Z002');

        $this->actingAs($this->user)
            ->get(route('factures-achat.index', ['archives' => 'include']))
            ->assertStatus(200)
            ->assertSee($actif->numero)
            ->assertSee($archive->numero);
    }

    public function test_index_archives_only_affiche_uniquement_archives(): void
    {
        $actif   = $this->makeFactureAchat('payee', 'ACH/2026/A003');
        $archive = $this->makeFactureAchat('archive', 'ACH/2026/Z003');

        $this->actingAs($this->user)
            ->get(route('factures-achat.index', ['archives' => 'only']))
            ->assertStatus(200)
            ->assertDontSee($actif->numero)
            ->assertSee($archive->numero);
    }

    public function test_filtre_statut_combine_avec_archives_include(): void
    {
        $payee   = $this->makeFactureAchat('payee', 'ACH/2026/P001');
        $attente = $this->makeFactureAchat('en_attente', 'ACH/2026/E001');
        $archive = $this->makeFactureAchat('archive', 'ACH/2026/Z004');

        $this->actingAs($this->user)
            ->get(route('factures-achat.index', ['archives' => 'include', 'statut' => 'payee']))
            ->assertStatus(200)
            ->assertSee($payee->numero)
            ->assertDontSee($attente->numero)
            ->assertDontSee($archive->numero);
    }
}
