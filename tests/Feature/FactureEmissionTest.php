<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FactureEmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'comptable', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->client = Client::create(['nom' => 'Client Test', 'actif' => true]);
    }

    private function creerBrouillonFacture(): Facture
    {
        $response = $this->post(route('factures.store'), [
            'client_id'       => $this->client->id,
            'date_document'   => now()->toDateString(),
            'date_echeance'   => now()->addDays(30)->toDateString(),
            'delai_reglement' => 30,
            'lignes' => [
                ['designation' => 'Prestation', 'quantite' => 1, 'prix_unitaire' => 100, 'taux_tva' => 21],
            ],
        ]);
        $response->assertRedirect();

        return Facture::orderByDesc('id')->first();
    }

    #[Test]
    public function test_facture_creee_en_brouillon_sans_numero(): void
    {
        $this->actingAs($this->user);

        $facture = $this->creerBrouillonFacture();

        $this->assertNotNull($facture);
        $this->assertEquals('brouillon', (string) $facture->statut);
        $this->assertNull($facture->numero);
    }

    #[Test]
    public function test_emettre_alloue_numero_et_transitionne(): void
    {
        $this->actingAs($this->user);

        $facture = $this->creerBrouillonFacture();

        $this->post(route('factures.emettre', $facture))
            ->assertRedirect(route('factures.show', $facture));

        $facture->refresh();
        $this->assertNotNull($facture->numero);
        $this->assertEquals('en_attente', (string) $facture->statut);
    }

    #[Test]
    public function test_emettre_refuse_si_deja_emise(): void
    {
        $this->actingAs($this->user);

        $facture = $this->creerBrouillonFacture();
        $this->post(route('factures.emettre', $facture));
        $facture->refresh();

        $this->assertEquals('en_attente', (string) $facture->statut);

        $this->post(route('factures.emettre', $facture))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    #[Test]
    public function test_emettre_format_numero_correct(): void
    {
        $this->actingAs($this->user);

        $facture = $this->creerBrouillonFacture();
        $this->post(route('factures.emettre', $facture));
        $facture->refresh();

        $this->assertMatchesRegularExpression('/^FAC\/\d{4}\/\d{4}$/', $facture->numero);
    }

    #[Test]
    public function test_numerotation_ininterrompue_apres_suppression_brouillon(): void
    {
        $this->actingAs($this->user);

        $f1 = $this->creerBrouillonFacture();
        $f2 = $this->creerBrouillonFacture();
        $f3 = $this->creerBrouillonFacture();

        // Supprimer le 2e brouillon
        $this->delete(route('factures.destroy', $f2))->assertRedirect();

        // Émettre le 1er
        $this->post(route('factures.emettre', $f1))->assertRedirect();
        $f1->refresh();

        // Émettre le 3e
        $this->post(route('factures.emettre', $f3))->assertRedirect();
        $f3->refresh();

        // Extraire les numéros de séquence
        preg_match('/FAC\/\d{4}\/(\d{4})/', $f1->numero, $m1);
        preg_match('/FAC\/\d{4}\/(\d{4})/', $f3->numero, $m3);

        $n1 = (int) $m1[1];
        $n3 = (int) $m3[1];

        // Les deux numéros doivent être consécutifs (différence = 1)
        $this->assertSame(1, $n3 - $n1, "Trou dans la séquence : {$f1->numero} puis {$f3->numero}");
    }

    #[Test]
    public function test_destroy_refuse_si_non_brouillon(): void
    {
        $this->actingAs($this->user);

        $facture = $this->creerBrouillonFacture();
        $this->post(route('factures.emettre', $facture));
        $facture->refresh();

        $this->assertEquals('en_attente', (string) $facture->statut);

        $this->delete(route('factures.destroy', $facture))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('factures', ['id' => $facture->id]);
    }

    #[Test]
    public function test_destroy_ok_si_brouillon(): void
    {
        $this->actingAs($this->user);

        $facture = $this->creerBrouillonFacture();
        $id = $facture->id;

        $this->delete(route('factures.destroy', $facture))
            ->assertRedirect(route('factures.index'));

        $this->assertSoftDeleted('factures', ['id' => $id]);
    }

    #[Test]
    public function test_update_ne_permet_pas_transition_sauvage(): void
    {
        $this->actingAs($this->user);

        $facture = $this->creerBrouillonFacture();
        $facture->load('lignes');

        $lignes = $facture->lignes->map(fn($l) => [
            'designation'   => $l->designation,
            'quantite'      => $l->quantite,
            'prix_unitaire' => $l->prix_unitaire,
            'taux_tva'      => $l->taux_tva,
        ])->toArray();

        $this->put(route('factures.update', $facture), [
            'statut'          => 'envoyee',
            'date_document'   => now()->toDateString(),
            'delai_reglement' => 30,
            'lignes'          => $lignes,
        ])->assertRedirect()
          ->assertSessionHas('error');

        $this->assertEquals('brouillon', (string) $facture->fresh()->statut);
    }
}
