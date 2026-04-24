<?php

namespace Tests\Feature;

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

class ConformiteWorkflowTest extends TestCase
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

    // ─── Scénario 1 : Devis archivé absent de l'index par défaut ─────────────

    public function test_devis_archive_exclu_de_index_par_defaut(): void
    {
        $actif   = Devis::create(['numero' => 'DEV/2026/0001', 'client_id' => $this->client->id, 'statut' => 'en_attente', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);
        $archive = Devis::create(['numero' => 'DEV/2026/0002', 'client_id' => $this->client->id, 'statut' => 'archive', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);

        $response = $this->actingAs($this->user)->get(route('devis.index'));

        $response->assertStatus(200);
        $response->assertSee($actif->numero);
        $response->assertDontSee($archive->numero);
    }

    // ─── Scénario 2 : BDC archivé absent de l'index par défaut ──────────────

    public function test_bdc_archive_exclu_de_index_par_defaut(): void
    {
        $actif   = BonCommande::create(['numero' => 'BDC/2026/0001', 'client_id' => $this->client->id, 'statut' => 'en_attente', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);
        $archive = BonCommande::create(['numero' => 'BDC/2026/0002', 'client_id' => $this->client->id, 'statut' => 'archive', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);

        $response = $this->actingAs($this->user)->get(route('bons-commande.index'));

        $response->assertStatus(200);
        $response->assertSee($actif->numero);
        $response->assertDontSee($archive->numero);
    }

    // ─── Scénario 3 : Facture archivée absente de l'index par défaut ─────────

    public function test_facture_archive_exclue_de_index_par_defaut(): void
    {
        $actif   = Facture::create(['numero' => 'FAC/2026/0001', 'client_id' => $this->client->id, 'statut' => 'en_attente', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0, 'montant_net_a_payer' => 0]);
        $archive = Facture::create(['numero' => 'FAC/2026/0002', 'client_id' => $this->client->id, 'statut' => 'archive', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0, 'montant_net_a_payer' => 0]);

        $response = $this->actingAs($this->user)->get(route('factures.index'));

        $response->assertStatus(200);
        $response->assertSee($actif->numero);
        $response->assertDontSee($archive->numero);
    }

    // ─── Scénario 4 : FactureAchat archivée absente de l'index par défaut ────

    public function test_facture_achat_archive_exclue_de_index_par_defaut(): void
    {
        $fournisseur = Fournisseur::create(['nom' => 'Fournisseur Test', 'actif' => true]);
        $actif   = FactureAchat::create(['numero' => 'ACH/2026/0001', 'fournisseur_id' => $fournisseur->id, 'statut' => 'en_attente', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0, 'categorie' => 'divers', 'taux_tva' => 21]);
        $archive = FactureAchat::create(['numero' => 'ACH/2026/0002', 'fournisseur_id' => $fournisseur->id, 'statut' => 'archive', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0, 'categorie' => 'divers', 'taux_tva' => 21]);

        $response = $this->actingAs($this->user)->get(route('factures-achat.index'));

        $response->assertStatus(200);
        $response->assertSee($actif->numero);
        $response->assertDontSee($archive->numero);
    }

    // ─── Scénario 5 : archives=include affiche tous les documents ────────────

    public function test_archives_include_affiche_actifs_et_archives(): void
    {
        $actif   = Devis::create(['numero' => 'DEV/2026/0003', 'client_id' => $this->client->id, 'statut' => 'en_attente', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);
        $archive = Devis::create(['numero' => 'DEV/2026/0004', 'client_id' => $this->client->id, 'statut' => 'archive', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);

        $response = $this->actingAs($this->user)->get(route('devis.index', ['archives' => 'include']));

        $response->assertStatus(200);
        $response->assertSee($actif->numero);
        $response->assertSee($archive->numero);
    }

    // ─── Scénario 6 : archives=only n'affiche que les archivés ───────────────

    public function test_archives_only_affiche_uniquement_les_archives(): void
    {
        $actif   = Devis::create(['numero' => 'DEV/2026/0005', 'client_id' => $this->client->id, 'statut' => 'en_attente', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);
        $archive = Devis::create(['numero' => 'DEV/2026/0006', 'client_id' => $this->client->id, 'statut' => 'archive', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);

        $response = $this->actingAs($this->user)->get(route('devis.index', ['archives' => 'only']));

        $response->assertStatus(200);
        $response->assertDontSee($actif->numero);
        $response->assertSee($archive->numero);
    }

    // ─── Scénario 7 : Archiver un devis ne supprime pas son BDC lié ──────────

    public function test_archiver_devis_ne_supprime_pas_bdc_lie(): void
    {
        $devis = Devis::create(['numero' => 'DEV/2026/0007', 'client_id' => $this->client->id, 'statut' => 'valide', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);
        $bdc   = BonCommande::create(['numero' => 'BDC/2026/0007', 'devis_id' => $devis->id, 'client_id' => $this->client->id, 'statut' => 'en_attente', 'date_document' => now()->toDateString(), 'montant_ht' => 0, 'montant_tva' => 0, 'montant_ttc' => 0]);

        $this->actingAs($this->user)
            ->patch(route('devis.archiver', $devis))
            ->assertRedirect();

        $this->assertDatabaseHas('devis', ['id' => $devis->id, 'statut' => 'archive']);
        $this->assertDatabaseHas('bons_commande', ['id' => $bdc->id]);
        $this->assertNull(BonCommande::find($bdc->id)->deleted_at);
    }
}
