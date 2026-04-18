<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Devis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalculatriceLigneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'utilisateur', 'guard_name' => 'web']);
    }

    private function userAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    private function client(): Client
    {
        return Client::create(['nom' => 'Test', 'email' => 't@t.be', 'actif' => true]);
    }

    public function test_trace_calcul_persiste_en_detail(): void
    {
        $this->actingAs($this->userAdmin())->post(route('devis.store'), [
            'client_id'     => $this->client()->id,
            'statut'        => 'brouillon',
            'date_document' => now()->toDateString(),
            'lignes' => [[
                'designation'   => 'Dalle 60x60',
                'detail'        => "Pose au sol\nQté : 12 * 6.25 = 75",
                'unite'         => 'm²',
                'quantite'      => 75,
                'prix_unitaire' => 45,
                'taux_tva'      => 21,
            ]],
        ]);

        $ligne = Devis::latest()->first()->lignes->first();

        $this->assertStringContainsString('Qté : 12 * 6.25 = 75', $ligne->detail);
        $this->assertEquals(75, (float) $ligne->quantite);
    }

    public function test_detail_avec_traces_multiples_persiste(): void
    {
        $detailCombine = "Mur extérieur côté jardin\nQté : 12 * 2.5 = 30\nPrix : 45 * 1.15 = 51.75";

        $this->actingAs($this->userAdmin())->post(route('devis.store'), [
            'client_id'     => $this->client()->id,
            'statut'        => 'brouillon',
            'date_document' => now()->toDateString(),
            'lignes' => [[
                'designation'   => 'Enduit façade',
                'detail'        => $detailCombine,
                'unite'         => 'm²',
                'quantite'      => 30,
                'prix_unitaire' => 51.75,
                'taux_tva'      => 21,
            ]],
        ]);

        $ligne = Devis::latest()->first()->lignes->first();

        $this->assertStringContainsString('Qté : 12 * 2.5 = 30', $ligne->detail);
        $this->assertStringContainsString('Prix : 45 * 1.15 = 51.75', $ligne->detail);
        $this->assertEquals(30, (float) $ligne->quantite);
        $this->assertEqualsWithDelta(51.75, (float) $ligne->prix_unitaire, 0.01);
    }
}
