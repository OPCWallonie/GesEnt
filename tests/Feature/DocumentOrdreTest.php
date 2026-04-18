<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Devis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentOrdreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    private function userAdmin(): User
    {
        $u = User::factory()->create();
        $u->assignRole('admin');
        return $u;
    }

    public function test_ordre_des_lignes_soumises_est_preserve(): void
    {
        $user   = $this->userAdmin();
        $client = Client::create(['nom' => 'Test', 'email' => 't@t.be', 'actif' => true]);

        $this->actingAs($user)->post(route('devis.store'), [
            'client_id'     => $client->id,
            'statut'        => 'brouillon',
            'date_document' => now()->toDateString(),
            'lignes' => [
                ['designation' => 'C', 'unite' => 'pc', 'quantite' => 1, 'prix_unitaire' => 10, 'taux_tva' => 21],
                ['designation' => 'A', 'unite' => 'pc', 'quantite' => 1, 'prix_unitaire' => 20, 'taux_tva' => 21],
                ['designation' => 'B', 'unite' => 'pc', 'quantite' => 1, 'prix_unitaire' => 30, 'taux_tva' => 21],
            ],
        ]);

        $devis  = Devis::latest()->first();
        $lignes = $devis->lignes()->orderBy('ordre')->get();

        $this->assertEquals(['C', 'A', 'B'], $lignes->pluck('designation')->toArray());
        $this->assertEquals([0, 1, 2], $lignes->pluck('ordre')->toArray());
    }

    public function test_sections_et_lignes_mixtes_preservent_ordre(): void
    {
        $user   = $this->userAdmin();
        $client = Client::create(['nom' => 'Test2', 'email' => 't2@t.be', 'actif' => true]);

        $this->actingAs($user)->post(route('devis.store'), [
            'client_id'     => $client->id,
            'statut'        => 'brouillon',
            'date_document' => now()->toDateString(),
            'lignes' => [
                ['designation' => 'Sanitaire', 'est_section' => '1', 'unite' => '—',  'quantite' => 0, 'prix_unitaire' => 0, 'taux_tva' => 21],
                ['designation' => 'Robinet',   'unite' => 'pc', 'quantite' => 2, 'prix_unitaire' => 89,  'taux_tva' => 21],
                ['designation' => 'Toiture',   'est_section' => '1', 'unite' => '—',  'quantite' => 0, 'prix_unitaire' => 0, 'taux_tva' => 21],
                ['designation' => 'Tuile',     'unite' => 'pc', 'quantite' => 500, 'prix_unitaire' => 1.2, 'taux_tva' => 21],
            ],
        ]);

        $devis  = Devis::latest()->first();
        $lignes = $devis->lignes()->orderBy('ordre')->get();

        $this->assertEquals(['Sanitaire', 'Robinet', 'Toiture', 'Tuile'], $lignes->pluck('designation')->toArray());
        $this->assertEquals([true, false, true, false], $lignes->pluck('est_section')->toArray());
    }
}
