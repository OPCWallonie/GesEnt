<?php

namespace Tests\Unit\States;

use App\Models\Client;
use App\Models\Facture;
use App\States\Facture\Brouillon;
use App\States\Facture\EnAttente;
use App\States\Facture\Envoyee;
use App\States\Facture\Payee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use Tests\TestCase;

class FactureStatutTest extends TestCase
{
    use RefreshDatabase;

    private function makeFacture(array $attrs = []): Facture
    {
        $client = Client::create(['nom' => 'Test Client', 'actif' => true]);

        return Facture::create(array_merge([
            'client_id'          => $client->id,
            'date_document'      => now()->toDateString(),
            'montant_ht'         => 1000,
            'montant_tva'        => 210,
            'montant_ttc'        => 1210,
            'montant_net_a_payer'=> 1210,
            'delai_reglement'    => 30,
        ], $attrs));
    }

    public function test_default_statut_est_brouillon(): void
    {
        $facture = $this->makeFacture();

        $this->assertTrue($facture->estBrouillon());
        $this->assertInstanceOf(Brouillon::class, $facture->statut);
        $this->assertNull($facture->numero);
    }

    public function test_transition_brouillon_vers_en_attente_ok(): void
    {
        $facture = $this->makeFacture();

        $facture->statut->transitionTo(EnAttente::class);
        $facture->refresh();

        $this->assertInstanceOf(EnAttente::class, $facture->statut);
        $this->assertEquals('en_attente', (string) $facture->statut);
    }

    public function test_transition_brouillon_vers_payee_interdite(): void
    {
        $this->expectException(TransitionNotFound::class);

        $facture = $this->makeFacture();
        $facture->statut->transitionTo(Payee::class);
    }

    public function test_transition_brouillon_vers_envoyee_interdite(): void
    {
        $this->expectException(TransitionNotFound::class);

        $facture = $this->makeFacture();
        $facture->statut->transitionTo(Envoyee::class);
    }

    public function test_peut_etre_emise_vrai_si_brouillon(): void
    {
        $facture = $this->makeFacture();

        $this->assertTrue($facture->peutEtreEmise());
    }

    public function test_peut_etre_emise_faux_si_en_attente(): void
    {
        $facture = $this->makeFacture();
        $facture->statut->transitionTo(EnAttente::class);
        $facture->refresh();

        $this->assertFalse($facture->peutEtreEmise());
    }
}
