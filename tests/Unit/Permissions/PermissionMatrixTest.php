<?php

namespace Tests\Unit\Permissions;

use App\Models\Avoir;
use App\Models\Avenant;
use App\Models\BonCommande;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function makeClient(): Client
    {
        return Client::create(['nom' => 'Test', 'actif' => true]);
    }

    private function makeDevis(array $attrs = []): Devis
    {
        return Devis::create(array_merge([
            'numero'        => 'DEV/2026/0001',
            'client_id'     => $this->makeClient()->id,
            'statut'        => 'en_attente',
            'date_document' => now()->toDateString(),
            'montant_ht'    => 1000,
            'montant_tva'   => 210,
            'montant_ttc'   => 1210,
        ], $attrs));
    }

    private function makeBdc(array $attrs = []): BonCommande
    {
        return BonCommande::create(array_merge([
            'numero'        => 'BDC/2026/0001',
            'client_id'     => $this->makeClient()->id,
            'statut'        => 'en_attente',
            'date_document' => now()->toDateString(),
            'montant_ht'    => 1000,
            'montant_tva'   => 210,
            'montant_ttc'   => 1210,
            'delai_reglement' => 30,
        ], $attrs));
    }

    private function makeFacture(array $attrs = []): Facture
    {
        return Facture::create(array_merge([
            'client_id'          => $this->makeClient()->id,
            'statut'             => 'brouillon',
            'date_document'      => now()->toDateString(),
            'montant_ht'         => 1000,
            'montant_tva'        => 210,
            'montant_ttc'        => 1210,
            'montant_net_a_payer' => 1210,
            'delai_reglement'    => 30,
        ], $attrs));
    }

    private function makeAvoir(array $attrs = []): Avoir
    {
        $facture = $this->makeFacture(['statut' => 'en_attente', 'numero' => 'FAC/2026/' . rand(1000, 9999)]);
        $user    = User::factory()->create();

        return Avoir::create(array_merge([
            'facture_id'    => $facture->id,
            'client_id'     => $facture->client_id,
            'created_by'    => $user->id,
            'statut'        => 'brouillon',
            'date_document' => now()->toDateString(),
            'motif'         => 'Test',
            'montant_ht'    => 100,
            'taux_tva'      => 21,
            'montant_tva'   => 21,
            'montant_ttc'   => 121,
        ], $attrs));
    }

    private function makeAvenant(array $attrs = []): \App\Models\Avenant
    {
        $bdc = $this->makeBdc();

        return \App\Models\Avenant::create(array_merge([
            'numero'         => 'AV/' . $bdc->numero . '/1',
            'bon_commande_id'=> $bdc->id,
            'numero_ordre'   => 1,
            'created_by'     => \App\Models\User::factory()->create()->id,
            'statut'         => 'en_attente',
            'date_document'  => now()->toDateString(),
            'montant_ht'     => 100,
            'montant_tva'    => 21,
            'montant_ttc'    => 121,
        ], $attrs));
    }

    private function makeFactureAchat(array $attrs = []): FactureAchat
    {
        $fournisseur = Fournisseur::create(['nom' => 'Test Fournisseur', 'actif' => true]);

        return FactureAchat::create(array_merge([
            'numero'         => 'FA/2026/0001',
            'fournisseur_id' => $fournisseur->id,
            'categorie'      => 'materiel',
            'date_document'  => now()->toDateString(),
            'montant_ht'     => 100,
            'taux_tva'       => 21,
            'montant_tva'    => 21,
            'montant_ttc'    => 121,
            'statut'         => 'en_attente',
            'peppol_source'  => 'manuel',
        ], $attrs));
    }

    // ─── Devis ───────────────────────────────────────────────────────────────

    public function test_devis_en_attente_peut_etre_modifie(): void
    {
        $devis = $this->makeDevis(['statut' => 'en_attente']);
        $this->assertTrue($devis->peutEtreModifie());
    }

    public function test_devis_archive_ne_peut_pas_etre_modifie(): void
    {
        $devis = $this->makeDevis(['statut' => 'archive']);
        $this->assertFalse($devis->peutEtreModifie());
    }

    public function test_devis_sans_bdc_peut_etre_supprime(): void
    {
        $devis = $this->makeDevis(['statut' => 'en_attente']);
        $this->assertTrue($devis->peutEtreSupprime());
    }

    public function test_devis_archive_ne_peut_pas_etre_supprime(): void
    {
        $devis = $this->makeDevis(['statut' => 'archive']);
        $this->assertFalse($devis->peutEtreSupprime());
    }

    public function test_devis_valide_peut_etre_archive(): void
    {
        $devis = $this->makeDevis(['statut' => 'valide']);
        $this->assertTrue($devis->peutEtreArchive());
    }

    public function test_devis_archive_ne_peut_pas_etre_archive_a_nouveau(): void
    {
        $devis = $this->makeDevis(['statut' => 'archive']);
        $this->assertFalse($devis->peutEtreArchive());
    }

    // ─── BonCommande ─────────────────────────────────────────────────────────

    public function test_bdc_sans_factures_peut_etre_modifie(): void
    {
        $bdc = $this->makeBdc();
        $this->assertTrue($bdc->peutEtreModifie());
    }

    public function test_bdc_archive_ne_peut_pas_etre_modifie(): void
    {
        $bdc = $this->makeBdc(['statut' => 'archive']);
        $this->assertFalse($bdc->peutEtreModifie());
    }

    public function test_bdc_sans_factures_peut_etre_supprime(): void
    {
        $bdc = $this->makeBdc();
        $this->assertTrue($bdc->peutEtreSupprime());
    }

    public function test_bdc_archive_ne_peut_pas_etre_supprime(): void
    {
        $bdc = $this->makeBdc(['statut' => 'archive']);
        $this->assertFalse($bdc->peutEtreSupprime());
    }

    public function test_bdc_actif_peut_etre_archive(): void
    {
        $bdc = $this->makeBdc(['statut' => 'en_attente']);
        $this->assertTrue($bdc->peutEtreArchive());
    }

    public function test_bdc_archive_ne_peut_pas_etre_archive_a_nouveau(): void
    {
        $bdc = $this->makeBdc(['statut' => 'archive']);
        $this->assertFalse($bdc->peutEtreArchive());
    }

    // ─── Facture ─────────────────────────────────────────────────────────────

    public function test_facture_brouillon_peut_etre_modifiee(): void
    {
        $f = $this->makeFacture(['statut' => 'brouillon']);
        $this->assertTrue($f->peutEtreModifie());
    }

    public function test_facture_emise_ne_peut_pas_etre_modifiee(): void
    {
        $f = $this->makeFacture(['statut' => 'en_attente', 'numero' => 'FAC/2026/0001']);
        $this->assertFalse($f->peutEtreModifie());
    }

    public function test_facture_brouillon_peut_etre_supprimee(): void
    {
        $f = $this->makeFacture(['statut' => 'brouillon']);
        $this->assertTrue($f->peutEtreSupprime());
    }

    public function test_facture_emise_ne_peut_pas_etre_supprimee(): void
    {
        $f = $this->makeFacture(['statut' => 'en_attente', 'numero' => 'FAC/2026/0001']);
        $this->assertFalse($f->peutEtreSupprime());
    }

    public function test_facture_en_attente_peut_etre_archivee(): void
    {
        $f = $this->makeFacture(['statut' => 'en_attente', 'numero' => 'FAC/2026/0001']);
        $this->assertTrue($f->peutEtreArchive());
    }

    public function test_facture_brouillon_ne_peut_pas_etre_archivee(): void
    {
        $f = $this->makeFacture(['statut' => 'brouillon']);
        $this->assertFalse($f->peutEtreArchive());
    }

    public function test_facture_archivee_ne_peut_pas_etre_archivee_a_nouveau(): void
    {
        $f = $this->makeFacture(['statut' => 'archive', 'numero' => 'FAC/2026/0001']);
        $this->assertFalse($f->peutEtreArchive());
    }

    // ─── Avoir ───────────────────────────────────────────────────────────────

    public function test_avoir_ne_peut_jamais_etre_modifie(): void
    {
        $a = $this->makeAvoir(['statut' => 'brouillon']);
        $this->assertFalse($a->peutEtreModifie());

        $a2 = $this->makeAvoir(['statut' => 'emis', 'numero' => 'AVO/2026/0001']);
        $this->assertFalse($a2->peutEtreModifie());
    }

    public function test_avoir_brouillon_peut_etre_supprime(): void
    {
        $a = $this->makeAvoir(['statut' => 'brouillon']);
        $this->assertTrue($a->peutEtreSupprime());
    }

    public function test_avoir_emis_ne_peut_pas_etre_supprime(): void
    {
        $a = $this->makeAvoir(['statut' => 'emis', 'numero' => 'AVO/2026/0001']);
        $this->assertFalse($a->peutEtreSupprime());
    }

    public function test_avoir_emis_peut_etre_archive(): void
    {
        $a = $this->makeAvoir(['statut' => 'emis', 'numero' => 'AVO/2026/0001']);
        $this->assertTrue($a->peutEtreArchive());
    }

    public function test_avoir_brouillon_ne_peut_pas_etre_archive(): void
    {
        $a = $this->makeAvoir(['statut' => 'brouillon']);
        $this->assertFalse($a->peutEtreArchive());
    }

    // ─── FactureAchat ────────────────────────────────────────────────────────

    public function test_facture_achat_manuelle_est_manuelle(): void
    {
        $fa = $this->makeFactureAchat(['peppol_source' => 'manuel']);
        $this->assertTrue($fa->estManuelle());
        $this->assertFalse($fa->estPeppol());
        $this->assertFalse($fa->estOdoo());
    }

    public function test_facture_achat_peppol_est_peppol(): void
    {
        $fa = $this->makeFactureAchat(['peppol_source' => 'peppol']);
        $this->assertFalse($fa->estManuelle());
        $this->assertTrue($fa->estPeppol());
    }

    public function test_facture_achat_odoo_est_odoo(): void
    {
        $fa = $this->makeFactureAchat(['odoo_move_id' => 'ODOO-123']);
        $this->assertFalse($fa->estManuelle());
        $this->assertTrue($fa->estOdoo());
    }

    public function test_facture_achat_manuelle_champs_editables_complets(): void
    {
        $fa = $this->makeFactureAchat(['peppol_source' => 'manuel']);
        $this->assertContains('montant_ht', $fa->champsEditables());
        $this->assertContains('fournisseur_id', $fa->champsEditables());
    }

    public function test_facture_achat_peppol_champs_editables_restreints(): void
    {
        $fa = $this->makeFactureAchat(['peppol_source' => 'peppol']);
        $this->assertContains('categorie', $fa->champsEditables());
        $this->assertNotContains('montant_ht', $fa->champsEditables());
        $this->assertNotContains('fournisseur_id', $fa->champsEditables());
    }

    public function test_facture_achat_en_attente_peut_etre_supprimee(): void
    {
        $fa = $this->makeFactureAchat(['statut' => 'en_attente']);
        $this->assertTrue($fa->peutEtreSupprime());
    }

    public function test_facture_achat_payee_ne_peut_pas_etre_supprimee(): void
    {
        $fa = $this->makeFactureAchat(['statut' => 'payee']);
        $this->assertFalse($fa->peutEtreSupprime());
    }

    public function test_facture_achat_archive_ne_peut_pas_etre_supprimee(): void
    {
        $fa = $this->makeFactureAchat(['statut' => 'archive']);
        $this->assertFalse($fa->peutEtreSupprime());
    }

    public function test_facture_achat_peut_etre_archivee(): void
    {
        $fa = $this->makeFactureAchat(['statut' => 'en_attente']);
        $this->assertTrue($fa->peutEtreArchive());
    }

    public function test_facture_achat_archivee_ne_peut_pas_etre_archivee_a_nouveau(): void
    {
        $fa = $this->makeFactureAchat(['statut' => 'archive']);
        $this->assertFalse($fa->peutEtreArchive());
    }

    // ─── Correctifs Conf-02-fix : Protection Peppol ──────────────────────────

    public function test_facture_peppol_envoyee_ne_peut_pas_etre_archivee(): void
    {
        $f = $this->makeFacture([
            'statut'           => 'en_attente',
            'numero'           => 'FAC/2026/0001',
            'peppol_envoye_at' => now(),
        ]);
        $this->assertTrue($f->estPeppolEnvoyee());
        $this->assertFalse($f->peutEtreArchive(),
            'Une facture transmise via Peppol ne doit pas être archivable.');
    }

    public function test_facture_sans_peppol_peut_etre_archivee(): void
    {
        $f = $this->makeFacture([
            'statut' => 'en_attente',
            'numero' => 'FAC/2026/0002',
        ]);
        $this->assertFalse($f->estPeppolEnvoyee());
        $this->assertTrue($f->peutEtreArchive());
    }

    public function test_avoir_peppol_envoye_ne_peut_pas_etre_archive(): void
    {
        $a = $this->makeAvoir([
            'statut'           => 'emis',
            'numero'           => 'AVO/2026/0001',
            'peppol_envoye_at' => now(),
        ]);
        $this->assertTrue($a->estPeppolEnvoye());
        $this->assertFalse($a->peutEtreArchive());
    }

    // ─── Correctifs Conf-02-fix : Facture d'achat synchronisée ───────────────

    public function test_facture_achat_peppol_ne_peut_pas_etre_supprimee(): void
    {
        $fa = $this->makeFactureAchat([
            'peppol_source' => 'peppol_inbound',
            'statut'        => 'en_attente',
        ]);
        $this->assertFalse($fa->peutEtreSupprime(),
            'Une facture d\'achat reçue via Peppol ne doit jamais être supprimable.');
    }

    public function test_facture_achat_odoo_ne_peut_pas_etre_supprimee(): void
    {
        $fa = $this->makeFactureAchat([
            'odoo_move_id' => 'ODOO-456',
            'statut'       => 'en_attente',
        ]);
        $this->assertFalse($fa->peutEtreSupprime(),
            'Une facture d\'achat synchronisée depuis Odoo ne doit jamais être supprimable.');
    }

    public function test_facture_achat_manuelle_en_attente_peut_etre_supprimee(): void
    {
        $fa = $this->makeFactureAchat([
            'peppol_source' => 'manuel',
            'statut'        => 'en_attente',
        ]);
        $this->assertTrue($fa->peutEtreSupprime());
    }

    // ─── Correctifs Conf-02-fix : Avenant matrice ────────────────────────────

    public function test_avenant_valide_ne_peut_pas_etre_modifie(): void
    {
        $avenant = $this->makeAvenant(['statut' => 'valide']);
        $this->assertFalse($avenant->peutEtreModifie());
    }

    public function test_avenant_valide_ne_peut_pas_etre_supprime(): void
    {
        $avenant = $this->makeAvenant(['statut' => 'valide']);
        $this->assertFalse($avenant->peutEtreSupprime());
    }

    public function test_avenant_brouillon_peut_etre_modifie(): void
    {
        $avenant = $this->makeAvenant(['statut' => 'brouillon']);
        $this->assertTrue($avenant->peutEtreModifie());
    }

    // ─── Correctifs Conf-02-fix : Devis validé non modifiable ────────────────

    public function test_devis_valide_ne_peut_pas_etre_modifie(): void
    {
        $devis = $this->makeDevis(['statut' => 'valide']);
        $this->assertFalse($devis->peutEtreModifie());
    }

    public function test_devis_refuse_ne_peut_pas_etre_modifie(): void
    {
        $devis = $this->makeDevis(['statut' => 'refuse']);
        $this->assertFalse($devis->peutEtreModifie());
    }

    // ─── Correctifs Conf-02-fix : BDC avec factures pas archivable ───────────

    public function test_bdc_avec_factures_ne_peut_pas_etre_archive_sauf_termine(): void
    {
        $bdc = $this->makeBdc(['statut' => 'valide']);

        \App\Models\Facture::create([
            'bon_commande_id'    => $bdc->id,
            'client_id'          => $bdc->client_id,
            'numero'             => 'FAC/2026/9999',
            'statut'             => 'en_attente',
            'date_document'      => now()->toDateString(),
            'montant_ht'         => 100,
            'montant_tva'        => 21,
            'montant_ttc'        => 121,
            'montant_net_a_payer'=> 121,
            'delai_reglement'    => 30,
        ]);

        $bdc->refresh();
        $this->assertFalse($bdc->peutEtreArchive(),
            'Un BDC avec facture active ne doit pas être archivable (sauf statut termine).');
    }

    public function test_bdc_termine_peut_toujours_etre_archive(): void
    {
        $bdc = $this->makeBdc(['statut' => 'termine']);
        $this->assertTrue($bdc->peutEtreArchive());
    }
}
