<?php

namespace Tests\Unit\States;

use App\Models\Avoir;
use App\Models\Client;
use App\Models\Facture;
use App\Models\User;
use App\States\Avoir\Brouillon;
use App\States\Avoir\Emis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use Tests\TestCase;

class AvoirStatutTest extends TestCase
{
    use RefreshDatabase;

    private function makeAvoir(): Avoir
    {
        $user   = User::factory()->create();
        $client = Client::create(['nom' => 'Test', 'actif' => true]);
        $facture = Facture::create([
            'client_id'          => $client->id,
            'date_document'      => now()->toDateString(),
            'montant_ht'         => 1000,
            'montant_tva'        => 210,
            'montant_ttc'        => 1210,
            'montant_net_a_payer'=> 1210,
            'delai_reglement'    => 30,
        ]);

        return Avoir::create([
            'facture_id'    => $facture->id,
            'client_id'     => $client->id,
            'created_by'    => $user->id,
            'date_document' => now()->toDateString(),
            'motif'         => 'Test avoir',
            'montant_ht'    => 100,
            'taux_tva'      => 21,
            'montant_tva'   => 21,
            'montant_ttc'   => 121,
        ]);
    }

    public function test_default_statut_est_brouillon(): void
    {
        $avoir = $this->makeAvoir();

        $this->assertTrue($avoir->estBrouillon());
        $this->assertInstanceOf(Brouillon::class, $avoir->statut);
        $this->assertNull($avoir->numero);
    }

    public function test_transition_brouillon_vers_emis_ok(): void
    {
        $avoir = $this->makeAvoir();

        $avoir->statut->transitionTo(Emis::class);
        $avoir->refresh();

        $this->assertInstanceOf(Emis::class, $avoir->statut);
        $this->assertTrue($avoir->estEmis());
    }

    public function test_transition_emis_vers_brouillon_interdite(): void
    {
        $this->expectException(TransitionNotFound::class);

        $avoir = $this->makeAvoir();
        $avoir->statut->transitionTo(Emis::class);
        $avoir->refresh();

        $avoir->statut->transitionTo(Brouillon::class);
    }
}
