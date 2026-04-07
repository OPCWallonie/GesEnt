<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Models\Avoir;
use App\Models\ParametresEntreprise;
use App\Models\Paiement;
use Illuminate\Support\Facades\Log;

class OdooSyncService
{
    public function __construct(
        private OdooService $odoo,
        private NumerotationService $numerotation,
    ) {}

    // ---------------------------------------------------------------
    // CLIENTS → Odoo (res.partner)
    // ---------------------------------------------------------------

    public function syncClient(Client $client): array
    {
        if (!$this->odoo->isConfigured()) {
            return ['success' => false, 'message' => 'Odoo non configuré'];
        }

        $values = [
            'name'          => $client->nom,
            'is_company'    => true,
            'customer_rank' => 1,
            'street'        => $client->adresse,
            'zip'           => $client->code_postal,
            'city'          => $client->ville,
            'phone'         => $client->telephone,
            'mobile'        => $client->gsm,
            'email'         => $client->email,
            'vat'           => $client->numero_tva,
            'website'       => $client->site_web,
            'ref'           => $client->code_client,
        ];

        $countryId = $this->findCountryId($client->pays ?? 'Belgique');
        if ($countryId) $values['country_id'] = $countryId;

        try {
            if ($client->odoo_partner_id) {
                $this->odoo->write('res.partner', $client->odoo_partner_id, $values);
                $client->update(['odoo_synced_at' => now()]);
                return ['success' => true, 'message' => "Client {$client->nom} mis à jour dans Odoo (ID {$client->odoo_partner_id})."];
            } else {
                if ($client->numero_tva) {
                    $existing = $this->odoo->searchRead('res.partner', [
                        ['vat', '=', $client->numero_tva],
                        ['is_company', '=', true],
                    ], ['id']);

                    if (!empty($existing)) {
                        $odooId = $existing[0]['id'];
                        $this->odoo->write('res.partner', $odooId, $values);
                        $client->update(['odoo_partner_id' => $odooId, 'odoo_synced_at' => now()]);
                        return ['success' => true, 'message' => "Client {$client->nom} lié au partner Odoo existant (ID {$odooId})."];
                    }
                }

                $odooId = $this->odoo->create('res.partner', $values);
                $client->update(['odoo_partner_id' => $odooId, 'odoo_synced_at' => now()]);
                return ['success' => true, 'message' => "Client {$client->nom} créé dans Odoo (ID {$odooId})."];
            }
        } catch (\Exception $e) {
            Log::error("Odoo sync client failed", ['client' => $client->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------
    // FOURNISSEURS → Odoo (res.partner supplier)
    // ---------------------------------------------------------------

    public function syncFournisseur(Fournisseur $fournisseur): array
    {
        if (!$this->odoo->isConfigured()) {
            return ['success' => false, 'message' => 'Odoo non configuré'];
        }

        $values = [
            'name'          => $fournisseur->nom,
            'is_company'    => true,
            'supplier_rank' => 1,
            'street'        => $fournisseur->adresse,
            'zip'           => $fournisseur->code_postal,
            'city'          => $fournisseur->ville,
            'phone'         => $fournisseur->telephone,
            'email'         => $fournisseur->email,
            'vat'           => $fournisseur->numero_tva,
        ];

        $countryId = $this->findCountryId($fournisseur->pays ?? 'Belgique');
        if ($countryId) $values['country_id'] = $countryId;

        try {
            if ($fournisseur->odoo_partner_id) {
                $this->odoo->write('res.partner', $fournisseur->odoo_partner_id, $values);
                $fournisseur->update(['odoo_synced_at' => now()]);
                return ['success' => true, 'message' => "Fournisseur mis à jour dans Odoo."];
            } else {
                if ($fournisseur->numero_tva) {
                    $existing = $this->odoo->searchRead('res.partner', [
                        ['vat', '=', $fournisseur->numero_tva],
                    ], ['id']);
                    if (!empty($existing)) {
                        $odooId = $existing[0]['id'];
                        $fournisseur->update(['odoo_partner_id' => $odooId, 'odoo_synced_at' => now()]);
                        return ['success' => true, 'message' => "Fournisseur lié au partner Odoo existant (ID {$odooId})."];
                    }
                }

                $odooId = $this->odoo->create('res.partner', $values);
                $fournisseur->update(['odoo_partner_id' => $odooId, 'odoo_synced_at' => now()]);
                return ['success' => true, 'message' => "Fournisseur créé dans Odoo (ID {$odooId})."];
            }
        } catch (\Exception $e) {
            Log::error("Odoo sync fournisseur failed", ['id' => $fournisseur->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------
    // FACTURES VENTE → Odoo (account.move out_invoice)
    // ---------------------------------------------------------------

    public function syncFacture(Facture $facture): array
    {
        if (!$this->odoo->isConfigured()) {
            return ['success' => false, 'message' => 'Odoo non configuré'];
        }

        $params = ParametresEntreprise::instance();
        $facture->load('client', 'lignes');

        if (!$facture->client->odoo_partner_id) {
            $clientSync = $this->syncClient($facture->client);
            if (!$clientSync['success']) {
                return ['success' => false, 'message' => "Impossible de sync le client : {$clientSync['message']}"];
            }
            $facture->client->refresh();
        }

        $mapping = $params->odoo_mapping ?? [];

        $invoiceLines = [];
        foreach ($facture->lignes->where('est_section', false) as $ligne) {
            $accountId = $this->findAccountId($mapping['compte_vente'] ?? '700000');
            $taxId = $this->findTaxId((float) $ligne->taux_tva, 'sale');

            $invoiceLines[] = [0, 0, [
                'name'       => $ligne->designation . ($ligne->detail ? " — {$ligne->detail}" : ''),
                'quantity'   => (float) $ligne->quantite,
                'price_unit' => (float) $ligne->prix_unitaire,
                'account_id' => $accountId,
                'tax_ids'    => $taxId ? [[6, 0, [$taxId]]] : [],
            ]];
        }

        if ($facture->frais_port > 0) {
            $invoiceLines[] = [0, 0, [
                'name'       => 'Frais de port',
                'quantity'   => 1,
                'price_unit' => (float) $facture->frais_port,
                'account_id' => $this->findAccountId($mapping['compte_frais_port'] ?? '700000'),
            ]];
        }

        $moveValues = [
            'move_type'        => 'out_invoice',
            'partner_id'       => $facture->client->odoo_partner_id,
            'invoice_date'     => $facture->date_document->format('Y-m-d'),
            'invoice_date_due' => $facture->date_echeance?->format('Y-m-d'),
            'ref'              => $facture->numero,
            'narration'        => $facture->notes,
            'journal_id'       => $this->findJournalId($mapping['journal_vente'] ?? 'SAJ', 'sale'),
            'invoice_line_ids' => $invoiceLines,
        ];

        try {
            if ($facture->odoo_move_id) {
                $this->odoo->write('account.move', $facture->odoo_move_id, $moveValues);
                $facture->update(['odoo_synced_at' => now()]);
                return ['success' => true, 'message' => "Facture {$facture->numero} mise à jour dans Odoo."];
            } else {
                $existing = $this->odoo->searchRead('account.move', [
                    ['ref', '=', $facture->numero],
                    ['move_type', '=', 'out_invoice'],
                ], ['id']);

                if (!empty($existing)) {
                    $odooId = $existing[0]['id'];
                    $facture->update(['odoo_move_id' => $odooId, 'odoo_synced_at' => now()]);
                    return ['success' => true, 'message' => "Facture liée à l'écriture Odoo existante (ID {$odooId})."];
                }

                $odooId = $this->odoo->create('account.move', $moveValues);
                $facture->update(['odoo_move_id' => $odooId, 'odoo_synced_at' => now()]);
                return ['success' => true, 'message' => "Facture {$facture->numero} créée dans Odoo (ID {$odooId})."];
            }
        } catch (\Exception $e) {
            Log::error("Odoo sync facture failed", ['facture' => $facture->numero, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------
    // AVOIRS → Odoo (account.move out_refund)
    // ---------------------------------------------------------------

    public function syncAvoir(Avoir $avoir): array
    {
        if (!$this->odoo->isConfigured()) {
            return ['success' => false, 'message' => 'Odoo non configuré'];
        }

        $avoir->load('client', 'facture');
        $params = ParametresEntreprise::instance();
        $mapping = $params->odoo_mapping ?? [];

        if (!$avoir->client->odoo_partner_id) {
            $this->syncClient($avoir->client);
            $avoir->client->refresh();
        }

        $accountId = $this->findAccountId($mapping['compte_vente'] ?? '700000');
        $taxId = $this->findTaxId((float) $avoir->taux_tva, 'sale');

        $moveValues = [
            'move_type'        => 'out_refund',
            'partner_id'       => $avoir->client->odoo_partner_id,
            'invoice_date'     => $avoir->date_document->format('Y-m-d'),
            'ref'              => $avoir->numero,
            'narration'        => "Avoir sur facture {$avoir->facture->numero} — {$avoir->motif}",
            'journal_id'       => $this->findJournalId($mapping['journal_vente'] ?? 'SAJ', 'sale'),
            'invoice_line_ids' => [[0, 0, [
                'name'       => $avoir->motif,
                'quantity'   => 1,
                'price_unit' => (float) $avoir->montant_ht,
                'account_id' => $accountId,
                'tax_ids'    => $taxId ? [[6, 0, [$taxId]]] : [],
            ]]],
        ];

        try {
            if ($avoir->odoo_move_id) {
                $this->odoo->write('account.move', $avoir->odoo_move_id, $moveValues);
            } else {
                $odooId = $this->odoo->create('account.move', $moveValues);
                $avoir->update(['odoo_move_id' => $odooId]);
            }
            $avoir->update(['odoo_synced_at' => now()]);
            return ['success' => true, 'message' => "Avoir {$avoir->numero} synchronisé dans Odoo."];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------
    // PAIEMENTS ← Odoo (récupérer les paiements enregistrés)
    // ---------------------------------------------------------------

    public function recupererPaiements(): array
    {
        if (!$this->odoo->isConfigured()) {
            return ['success' => false, 'importes' => 0, 'message' => 'Odoo non configuré'];
        }

        $importes = 0;

        $factures = Facture::whereNotNull('odoo_move_id')
            ->whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
            ->get();

        foreach ($factures as $facture) {
            try {
                $moves = $this->odoo->read('account.move', [$facture->odoo_move_id], [
                    'payment_state', 'amount_residual', 'amount_total',
                ]);

                if (empty($moves)) continue;
                $move = $moves[0];

                $residual = (float) ($move['amount_residual'] ?? 0);
                $total    = (float) ($move['amount_total'] ?? 0);
                $paye     = $total - $residual;

                if ($paye > $facture->montant_total_paye) {
                    $montantNouveau = $paye - $facture->montant_total_paye;

                    Paiement::create([
                        'facture_id'    => $facture->id,
                        'date_paiement' => now()->toDateString(),
                        'montant'       => $montantNouveau,
                        'mode'          => 'odoo',
                        'reference'     => "Sync Odoo #{$facture->odoo_move_id}",
                        'notes'         => 'Paiement importé depuis Odoo',
                    ]);

                    $facture->recalculerPaiements();
                    $importes++;
                }
            } catch (\Exception $e) {
                Log::warning("Odoo payment sync failed for facture {$facture->numero}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success'  => true,
            'importes' => $importes,
            'message'  => "{$importes} paiement(s) importé(s) depuis Odoo.",
        ];
    }

    // ---------------------------------------------------------------
    // FACTURES ACHAT ← Odoo (récupérer les factures fournisseurs)
    // ---------------------------------------------------------------

    public function recupererFacturesAchat(): array
    {
        if (!$this->odoo->isConfigured()) {
            return ['success' => false, 'importees' => 0, 'message' => 'Odoo non configuré'];
        }

        $importees = 0;

        $derniereSync = FactureAchat::whereNotNull('odoo_move_id')
            ->max('odoo_synced_at');

        $domain = [
            ['move_type', '=', 'in_invoice'],
            ['state', '=', 'posted'],
        ];

        if ($derniereSync) {
            $domain[] = ['write_date', '>', $derniereSync];
        }

        $odooFactures = $this->odoo->searchRead('account.move', $domain, [
            'id', 'ref', 'partner_id', 'invoice_date', 'invoice_date_due',
            'amount_untaxed', 'amount_tax', 'amount_total',
            'payment_state', 'narration',
        ], ['limit' => 100]);

        foreach ($odooFactures as $odooFacture) {
            if (FactureAchat::where('odoo_move_id', $odooFacture['id'])->exists()) {
                continue;
            }

            $partnerId   = is_array($odooFacture['partner_id']) ? $odooFacture['partner_id'][0] : $odooFacture['partner_id'];
            $partnerName = is_array($odooFacture['partner_id']) ? $odooFacture['partner_id'][1] : 'Fournisseur inconnu';

            $fournisseur = Fournisseur::where('odoo_partner_id', $partnerId)->first();
            if (!$fournisseur) {
                $partners = $this->odoo->read('res.partner', [$partnerId], [
                    'name', 'vat', 'street', 'zip', 'city', 'email', 'phone',
                ]);
                $partner = $partners[0] ?? ['name' => $partnerName];

                if (!empty($partner['vat'])) {
                    $fournisseur = Fournisseur::where('numero_tva', $partner['vat'])->first();
                }

                if (!$fournisseur) {
                    $fournisseur = Fournisseur::create([
                        'nom'             => $partner['name'] ?? $partnerName,
                        'numero_tva'      => $partner['vat'] ?? null,
                        'adresse'         => $partner['street'] ?? null,
                        'code_postal'     => $partner['zip'] ?? null,
                        'ville'           => $partner['city'] ?? null,
                        'email'           => $partner['email'] ?? null,
                        'telephone'       => $partner['phone'] ?? null,
                        'odoo_partner_id' => $partnerId,
                        'actif'           => true,
                        'notes'           => 'Importé depuis Odoo',
                    ]);
                } else {
                    $fournisseur->update(['odoo_partner_id' => $partnerId]);
                }
            }

            $ht     = (float) ($odooFacture['amount_untaxed'] ?? 0);
            $tva    = (float) ($odooFacture['amount_tax'] ?? 0);
            $tauxTva = $ht > 0 ? round(($tva / $ht) * 100) : 21;

            FactureAchat::create([
                'numero'                => $this->numerotation->suivant('facture_achat'),
                'fournisseur_id'        => $fournisseur->id,
                'reference_fournisseur' => $odooFacture['ref'] ?? null,
                'categorie'             => 'materiel',
                'date_document'         => $odooFacture['invoice_date'] ?? now()->toDateString(),
                'date_echeance'         => $odooFacture['invoice_date_due'] ?? null,
                'montant_ht'            => round($ht, 2),
                'taux_tva'              => $tauxTva,
                'montant_tva'           => round($tva, 2),
                'montant_ttc'           => round((float) ($odooFacture['amount_total'] ?? 0), 2),
                'statut'                => ($odooFacture['payment_state'] ?? '') === 'paid' ? 'payee' : 'en_attente',
                'notes'                 => 'Importée depuis Odoo',
                'odoo_move_id'          => $odooFacture['id'],
                'odoo_synced_at'        => now(),
                'peppol_source'         => 'manuel',
            ]);

            $importees++;
        }

        return [
            'success'   => true,
            'importees' => $importees,
            'message'   => "{$importees} facture(s) d'achat importée(s) depuis Odoo.",
        ];
    }

    // ---------------------------------------------------------------
    // Helpers pour trouver les IDs Odoo
    // ---------------------------------------------------------------

    private function findCountryId(string $pays): ?int
    {
        $map = [
            'belgique'  => 'BE', 'belgium'  => 'BE', 'belgië' => 'BE',
            'france'    => 'FR', 'luxembourg' => 'LU',
            'pays-bas'  => 'NL', 'allemagne'  => 'DE',
        ];
        $code = $map[strtolower(trim($pays))] ?? strtoupper(substr($pays, 0, 2));

        $result = $this->odoo->searchRead('res.country', [['code', '=', $code]], ['id'], ['limit' => 1]);
        return $result[0]['id'] ?? null;
    }

    private function findAccountId(string $code): ?int
    {
        $result = $this->odoo->searchRead('account.account', [['code', '=', $code]], ['id'], ['limit' => 1]);
        return $result[0]['id'] ?? null;
    }

    private function findJournalId(string $code, string $type): ?int
    {
        $result = $this->odoo->searchRead('account.journal', [
            ['code', '=', $code],
            ['type', '=', $type],
        ], ['id'], ['limit' => 1]);
        return $result[0]['id'] ?? null;
    }

    private function findTaxId(float $taux, string $typeTax = 'sale'): ?int
    {
        $result = $this->odoo->searchRead('account.tax', [
            ['amount', '=', $taux],
            ['type_tax_use', '=', $typeTax],
        ], ['id'], ['limit' => 1]);
        return $result[0]['id'] ?? null;
    }
}
