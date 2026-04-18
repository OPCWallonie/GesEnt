<?php

namespace Tests\Feature;

use App\Models\DocumentDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentDraftTest extends TestCase
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

    public function test_save_cree_un_draft(): void
    {
        $this->actingAs($this->userAdmin())
            ->postJson(route('drafts.save'), [
                'document_type' => 'devis',
                'document_id'   => null,
                'data'          => ['client_id' => 1, 'statut' => 'brouillon'],
            ])
            ->assertOk()
            ->assertJsonStructure(['ok', 'saved_at']);

        $this->assertEquals(1, DocumentDraft::count());
    }

    public function test_save_deuxieme_fois_met_a_jour_au_lieu_de_creer(): void
    {
        $user = $this->userAdmin();

        $this->actingAs($user)->postJson(route('drafts.save'), [
            'document_type' => 'devis', 'document_id' => null, 'data' => ['v' => 1],
        ]);

        $this->actingAs($user)->postJson(route('drafts.save'), [
            'document_type' => 'devis', 'document_id' => null, 'data' => ['v' => 2],
        ]);

        $this->assertEquals(1, DocumentDraft::count());
        $this->assertEquals(['v' => 2], DocumentDraft::first()->data);
    }

    public function test_load_retourne_draft_si_existe(): void
    {
        $user = $this->userAdmin();
        DocumentDraft::create([
            'user_id'       => $user->id,
            'document_type' => 'devis',
            'document_id'   => null,
            'data'          => ['client_id' => 42],
            'saved_at'      => now()->subMinutes(5),
        ]);

        $this->actingAs($user)
            ->getJson(route('drafts.load') . '?document_type=devis')
            ->assertOk()
            ->assertJsonPath('draft.data.client_id', 42)
            ->assertJsonPath('draft.age_minutes', 5);
    }

    public function test_load_ne_retourne_pas_draft_obsolete(): void
    {
        $user = $this->userAdmin();
        DocumentDraft::create([
            'user_id'       => $user->id,
            'document_type' => 'devis',
            'document_id'   => null,
            'data'          => ['v' => 1],
            'saved_at'      => now()->subDays(3),
        ]);

        $this->actingAs($user)
            ->getJson(route('drafts.load') . '?document_type=devis')
            ->assertOk()
            ->assertJsonPath('draft', null);
    }

    public function test_draft_isole_par_user(): void
    {
        $userA = $this->userAdmin();
        $userB = $this->userAdmin();

        $this->actingAs($userA)->postJson(route('drafts.save'), [
            'document_type' => 'devis', 'document_id' => null, 'data' => ['secret' => 'A'],
        ]);

        $this->actingAs($userB)
            ->getJson(route('drafts.load') . '?document_type=devis')
            ->assertJsonPath('draft', null);
    }

    public function test_destroy_supprime_uniquement_draft_cible(): void
    {
        $user = $this->userAdmin();

        DocumentDraft::create([
            'user_id' => $user->id, 'document_type' => 'devis',    'document_id' => null,
            'data' => [], 'saved_at' => now(),
        ]);
        DocumentDraft::create([
            'user_id' => $user->id, 'document_type' => 'facture',  'document_id' => null,
            'data' => [], 'saved_at' => now(),
        ]);

        $this->actingAs($user)->deleteJson(route('drafts.destroy'), [
            'document_type' => 'devis', 'document_id' => null,
        ]);

        $this->assertEquals(1, DocumentDraft::count());
        $this->assertEquals('facture', DocumentDraft::first()->document_type);
    }

    public function test_payload_trop_volumineux_rejete(): void
    {
        $this->actingAs($this->userAdmin())
            ->postJson(route('drafts.save'), [
                'document_type' => 'devis',
                'data'          => ['huge' => str_repeat('x', 600_000)],
            ])
            ->assertStatus(413);
    }
}
