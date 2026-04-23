<?php

namespace Tests\Feature;

use App\Models\Avoir;
use App\Models\BonCommande;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArchivageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->user   = User::factory()->create();
        $this->user->assignRole('admin');
        $this->client = Client::create(['nom' => 'Client Test', 'actif' => true]);
    }

    // ─── Devis ───────────────────────────────────────────────────────────────

    private function makeDevis(string $statut = 'en_attente'): Devis
    {
        return Devis::create([
            'numero'        => 'DEV/2026/' . rand(1000, 9999),
            'client_id'     => $this->client->id,
            'statut'        => $statut,
            'date_document' => now()->toDateString(),
            'montant_ht'    => 1000,
            'montant_tva'   => 210,
            'montant_ttc'   => 1210,
        ]);
    }

    public function test_archiver_devis_ok(): void
    {
        $this->actingAs($this->user);

        $devis = $this->makeDevis('en_attente');

        $this->patch(route('devis.archiver', $devis))
            ->assertRedirect(route('devis.show', $devis));

        $this->assertEquals('archive', (string) $devis->fresh()->statut);
    }

    public function test_archiver_devis_deja_archive_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $devis = $this->makeDevis('archive');

        $this->patch(route('devis.archiver', $devis))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_destroy_devis_archive_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $devis = $this->makeDevis('archive');

        $this->delete(route('devis.destroy', $devis))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('devis', ['id' => $devis->id]);
    }

    public function test_destroy_devis_actif_ok(): void
    {
        $this->actingAs($this->user);

        $devis = $this->makeDevis('en_attente');
        $id    = $devis->id;

        $this->delete(route('devis.destroy', $devis))
            ->assertRedirect(route('devis.index'));

        $this->assertSoftDeleted('devis', ['id' => $id]);
    }

    // ─── BonCommande ─────────────────────────────────────────────────────────

    private function makeBdc(string $statut = 'en_attente'): BonCommande
    {
        return BonCommande::create([
            'numero'          => 'BDC/2026/' . rand(1000, 9999),
            'client_id'       => $this->client->id,
            'statut'          => $statut,
            'date_document'   => now()->toDateString(),
            'montant_ht'      => 1000,
            'montant_tva'     => 210,
            'montant_ttc'     => 1210,
            'delai_reglement' => 30,
        ]);
    }

    public function test_archiver_bdc_ok(): void
    {
        $this->actingAs($this->user);

        $bdc = $this->makeBdc('en_attente');

        $this->patch(route('bons-commande.archiver', $bdc))
            ->assertRedirect(route('bons-commande.show', $bdc));

        $this->assertEquals('archive', (string) $bdc->fresh()->statut);
    }

    public function test_archiver_bdc_deja_archive_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $bdc = $this->makeBdc('archive');

        $this->patch(route('bons-commande.archiver', $bdc))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_destroy_bdc_archive_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $bdc = $this->makeBdc('archive');

        $this->delete(route('bons-commande.destroy', $bdc))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bons_commande', ['id' => $bdc->id]);
    }

    // ─── Facture ─────────────────────────────────────────────────────────────

    private function makeFactureEmise(): Facture
    {
        return Facture::create([
            'client_id'           => $this->client->id,
            'numero'              => 'FAC/2026/' . rand(1000, 9999),
            'statut'              => 'en_attente',
            'date_document'       => now()->toDateString(),
            'montant_ht'          => 1000,
            'montant_tva'         => 210,
            'montant_ttc'         => 1210,
            'montant_net_a_payer' => 1210,
            'delai_reglement'     => 30,
        ]);
    }

    public function test_archiver_facture_emise_ok(): void
    {
        $this->actingAs($this->user);

        $facture = $this->makeFactureEmise();

        $this->patch(route('factures.archiver', $facture))
            ->assertRedirect(route('factures.show', $facture));

        $this->assertEquals('archive', (string) $facture->fresh()->statut);
    }

    public function test_archiver_facture_brouillon_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('factures.store'), [
            'client_id'       => $this->client->id,
            'date_document'   => now()->toDateString(),
            'date_echeance'   => now()->addDays(30)->toDateString(),
            'delai_reglement' => 30,
            'lignes'          => [
                ['designation' => 'Test', 'quantite' => 1, 'prix_unitaire' => 100, 'taux_tva' => 21],
            ],
        ]);
        $facture = Facture::orderByDesc('id')->first();

        $this->patch(route('factures.archiver', $facture))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals('brouillon', (string) $facture->fresh()->statut);
    }

    public function test_edit_facture_non_brouillon_bloque(): void
    {
        $this->actingAs($this->user);

        $facture = $this->makeFactureEmise();

        $this->get(route('factures.edit', $facture))
            ->assertRedirect(route('factures.show', $facture))
            ->assertSessionHas('error');
    }

    // ─── Avoir ───────────────────────────────────────────────────────────────

    private function makeAvoirEmis(): Avoir
    {
        $facture = $this->makeFactureEmise();

        return Avoir::create([
            'facture_id'    => $facture->id,
            'client_id'     => $this->client->id,
            'created_by'    => $this->user->id,
            'numero'        => 'AVO/2026/' . rand(1000, 9999),
            'statut'        => 'emis',
            'date_document' => now()->toDateString(),
            'motif'         => 'Test',
            'montant_ht'    => 100,
            'taux_tva'      => 21,
            'montant_tva'   => 21,
            'montant_ttc'   => 121,
        ]);
    }

    public function test_archiver_avoir_emis_ok(): void
    {
        $this->actingAs($this->user);

        $avoir = $this->makeAvoirEmis();

        $this->patch(route('avoirs.archiver', $avoir))
            ->assertRedirect(route('avoirs.show', $avoir));

        $this->assertEquals('archive', (string) $avoir->fresh()->statut);
    }

    public function test_archiver_avoir_brouillon_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $facture = $this->makeFactureEmise();

        $this->post(route('avoirs.store', $facture), [
            'date_document' => now()->toDateString(),
            'motif'         => 'Test',
            'montant_ht'    => 50,
            'taux_tva'      => 21,
        ]);

        $avoir = Avoir::orderByDesc('id')->first();

        $this->patch(route('avoirs.archiver', $avoir))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ─── FactureAchat ────────────────────────────────────────────────────────

    private function makeFactureAchat(string $statut = 'en_attente', array $attrs = []): FactureAchat
    {
        $fournisseur = Fournisseur::firstOrCreate(['nom' => 'Test Fournisseur'], ['actif' => true]);

        return FactureAchat::create(array_merge([
            'numero'         => 'FA/2026/' . rand(1000, 9999),
            'fournisseur_id' => $fournisseur->id,
            'categorie'      => 'materiel',
            'date_document'  => now()->toDateString(),
            'montant_ht'     => 100,
            'taux_tva'       => 21,
            'montant_tva'    => 21,
            'montant_ttc'    => 121,
            'statut'         => $statut,
            'peppol_source'  => 'manuel',
            'created_by'     => $this->user->id,
        ], $attrs));
    }

    public function test_archiver_facture_achat_ok(): void
    {
        $this->actingAs($this->user);

        $fa = $this->makeFactureAchat('en_attente');

        $this->patch(route('factures-achat.archiver', $fa))
            ->assertRedirect(route('factures-achat.show', $fa));

        $this->assertEquals('archive', $fa->fresh()->statut);
    }

    public function test_archiver_facture_achat_deja_archivee_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $fa = $this->makeFactureAchat('archive');

        $this->patch(route('factures-achat.archiver', $fa))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_destroy_facture_achat_payee_retourne_erreur(): void
    {
        $this->actingAs($this->user);

        $fa = $this->makeFactureAchat('payee');

        $this->delete(route('factures-achat.destroy', $fa))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('factures_achat', ['id' => $fa->id]);
    }

    public function test_destroy_facture_achat_en_attente_ok(): void
    {
        $this->actingAs($this->user);

        $fa = $this->makeFactureAchat('en_attente');
        $id = $fa->id;

        $this->delete(route('factures-achat.destroy', $fa))
            ->assertRedirect(route('factures-achat.index'));

        $this->assertSoftDeleted('factures_achat', ['id' => $id]);
    }

    public function test_edit_facture_achat_archivee_bloque(): void
    {
        $this->actingAs($this->user);

        $fa = $this->makeFactureAchat('archive');

        $this->get(route('factures-achat.edit', $fa))
            ->assertRedirect(route('factures-achat.show', $fa))
            ->assertSessionHas('error');
    }
}
