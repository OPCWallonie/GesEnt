<?php

namespace Tests\Feature;

use App\Models\Avoir;
use App\Models\Client;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AvoirEmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Facture $facture;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->client = Client::create(['nom' => 'Client Test', 'actif' => true]);

        // Créer une facture émise (en_attente) pour y rattacher des avoirs
        $this->facture = Facture::create([
            'client_id'          => $this->client->id,
            'numero'             => 'FAC/2026/0001',
            'statut'             => 'en_attente',
            'date_document'      => now()->toDateString(),
            'montant_ht'         => 1000,
            'montant_tva'        => 210,
            'montant_ttc'        => 1210,
            'montant_net_a_payer'=> 1210,
            'delai_reglement'    => 30,
        ]);
    }

    private function creerBrouillonAvoir(): Avoir
    {
        $this->post(route('avoirs.store', $this->facture), [
            'date_document' => now()->toDateString(),
            'motif'         => 'Correction erreur de facturation',
            'montant_ht'    => 100,
            'taux_tva'      => 21,
        ]);

        return Avoir::orderByDesc('id')->first();
    }

    #[Test]
    public function test_avoir_cree_en_brouillon_sans_numero(): void
    {
        $this->actingAs($this->user);

        $avoir = $this->creerBrouillonAvoir();

        $this->assertNotNull($avoir);
        $this->assertEquals('brouillon', (string) $avoir->statut);
        $this->assertNull($avoir->numero);
    }

    #[Test]
    public function test_emettre_avoir_alloue_numero_et_transitionne(): void
    {
        $this->actingAs($this->user);

        $avoir = $this->creerBrouillonAvoir();

        $this->post(route('avoirs.emettre', $avoir))
            ->assertRedirect(route('avoirs.show', $avoir));

        $avoir->refresh();
        $this->assertNotNull($avoir->numero);
        $this->assertEquals('emis', (string) $avoir->statut);
    }

    #[Test]
    public function test_emettre_avoir_refuse_si_deja_emis(): void
    {
        $this->actingAs($this->user);

        $avoir = $this->creerBrouillonAvoir();
        $this->post(route('avoirs.emettre', $avoir));

        $this->post(route('avoirs.emettre', $avoir))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    #[Test]
    public function test_emettre_avoir_format_numero_correct(): void
    {
        $this->actingAs($this->user);

        $avoir = $this->creerBrouillonAvoir();
        $this->post(route('avoirs.emettre', $avoir));
        $avoir->refresh();

        $this->assertMatchesRegularExpression('/^AVO\/\d{4}\/\d{4}$/', $avoir->numero);
    }

    #[Test]
    public function test_destroy_avoir_refuse_si_emis(): void
    {
        $this->actingAs($this->user);

        $avoir = $this->creerBrouillonAvoir();
        $this->post(route('avoirs.emettre', $avoir));
        $avoir->refresh();

        $this->delete(route('avoirs.destroy', $avoir))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('avoirs', ['id' => $avoir->id]);
    }

    #[Test]
    public function test_destroy_avoir_ok_si_brouillon(): void
    {
        $this->actingAs($this->user);

        $avoir = $this->creerBrouillonAvoir();
        $id = $avoir->id;

        $this->delete(route('avoirs.destroy', $avoir))
            ->assertRedirect(route('factures.show', $this->facture));

        $this->assertSoftDeleted('avoirs', ['id' => $id]);
    }

    #[Test]
    public function test_numerotation_avoir_ininterrompue_apres_suppression_brouillon(): void
    {
        $this->actingAs($this->user);

        $a1 = $this->creerBrouillonAvoir();
        $a2 = $this->creerBrouillonAvoir();
        $a3 = $this->creerBrouillonAvoir();

        // Supprimer le 2e
        $this->delete(route('avoirs.destroy', $a2))->assertRedirect();

        // Émettre le 1er
        $this->post(route('avoirs.emettre', $a1))->assertRedirect();
        $a1->refresh();

        // Émettre le 3e
        $this->post(route('avoirs.emettre', $a3))->assertRedirect();
        $a3->refresh();

        preg_match('/AVO\/\d{4}\/(\d{4})/', $a1->numero, $m1);
        preg_match('/AVO\/\d{4}\/(\d{4})/', $a3->numero, $m3);

        $n1 = (int) $m1[1];
        $n3 = (int) $m3[1];

        $this->assertSame(1, $n3 - $n1, "Trou dans la séquence : {$a1->numero} puis {$a3->numero}");
    }
}
