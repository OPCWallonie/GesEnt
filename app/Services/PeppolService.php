<?php

namespace App\Services;

use App\Models\Avoir;
use App\Models\Facture;
use App\Models\ParametresEntreprise;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeppolService
{
    private const URLS = [
        'storecove' => [
            'sandbox'    => 'https://api-sandbox.storecove.com/api/v2',
            'production' => 'https://api.storecove.com/api/v2',
        ],
        'billit' => [
            'sandbox'    => 'https://app.billit.eu/api/v1',
            'production' => 'https://app.billit.eu/api/v1',
        ],
        'einvoice_be' => [
            'sandbox'    => 'https://api.sandbox.e-invoice.be/v1',
            'production' => 'https://api.e-invoice.be/v1',
        ],
    ];

    public function __construct(private DocumentService $documentService) {}

    /**
     * Vérifie si le destinataire est joignable sur le réseau Peppol.
     */
    public function destinataireDisponible(string $numeroTva): bool
    {
        $params = ParametresEntreprise::instance();
        if (!$params->peppolActif()) {
            return false;
        }

        $provider = $params->peppol_provider;
        $apiKey   = $params->peppol_api_key_decrypte;
        $baseUrl  = self::URLS[$provider][$params->peppol_environment] ?? null;

        if (!$baseUrl || !$apiKey) {
            return false;
        }

        try {
            return match ($provider) {
                'storecove' => $this->discoveryStorecove($baseUrl, $apiKey, $numeroTva),
                'billit'    => $this->discoveryBillit($baseUrl, $apiKey, $numeroTva),
                default     => false,
            };
        } catch (\Exception $e) {
            Log::warning("Peppol discovery failed", ['tva' => $numeroTva, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Envoyer une facture via Peppol.
     *
     * @return array{success: bool, message: string, reference: ?string}
     */
    public function envoyerFacture(Facture $facture): array
    {
        $params = ParametresEntreprise::instance();

        if (!$params->peppolActif()) {
            return ['success' => false, 'message' => 'Peppol non configuré.', 'reference' => null];
        }

        // Si Peppol est délégué à Odoo, ne pas envoyer depuis Gesent
        if ($params->peppolGereParOdoo() && $facture->odoo_move_id) {
            return [
                'success'   => false,
                'message'   => 'Envoi Peppol géré par Odoo pour cette facture.',
                'reference' => null,
            ];
        }

        $provider    = $params->peppol_provider;
        $apiKey      = $params->peppol_api_key_decrypte;
        $entityId    = $params->peppol_entity_id;
        $baseUrl     = self::URLS[$provider][$params->peppol_environment] ?? null;

        if (!$baseUrl || !$apiKey || !$entityId) {
            return ['success' => false, 'message' => 'Configuration Peppol incomplète.', 'reference' => null];
        }

        $facture->load('client', 'chantier', 'lignes', 'modePaiement', 'bonCommande.avenants');

        try {
            return match ($provider) {
                'storecove'   => $this->envoyerStorecove($facture, $baseUrl, $apiKey, $entityId, $params),
                'billit'      => $this->envoyerBillit($facture, $baseUrl, $apiKey, $entityId, $params),
                'einvoice_be' => $this->envoyerEinvoiceBe($facture, $baseUrl, $apiKey, $entityId, $params),
                default       => ['success' => false, 'message' => "Provider inconnu : {$provider}", 'reference' => null],
            };
        } catch (\Exception $e) {
            Log::error("Peppol send failed", [
                'facture'  => $facture->numero,
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Erreur Peppol : ' . $e->getMessage(), 'reference' => null];
        }
    }

    /**
     * Envoyer un avoir (note de crédit) via Peppol.
     *
     * @return array{success: bool, message: string, reference: ?string}
     */
    public function envoyerAvoir(Avoir $avoir): array
    {
        $params = ParametresEntreprise::instance();

        if (!$params->peppolActif()) {
            return ['success' => false, 'message' => 'Peppol non configuré.', 'reference' => null];
        }

        $provider = $params->peppol_provider;
        $apiKey   = $params->peppol_api_key_decrypte;
        $entityId = $params->peppol_entity_id;
        $baseUrl  = self::URLS[$provider][$params->peppol_environment] ?? null;

        if (!$baseUrl || !$apiKey || !$entityId) {
            return ['success' => false, 'message' => 'Configuration Peppol incomplète.', 'reference' => null];
        }

        $avoir->load('client', 'facture', 'chantier');

        try {
            return match ($provider) {
                'storecove' => $this->envoyerAvoirStorecove($avoir, $baseUrl, $apiKey, $entityId, $params),
                default     => ['success' => false, 'message' => "Provider {$provider} : avoirs pas encore supportés.", 'reference' => null],
            };
        } catch (\Exception $e) {
            Log::error("Peppol credit note send failed", [
                'avoir'    => $avoir->numero,
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Erreur Peppol : ' . $e->getMessage(), 'reference' => null];
        }
    }

    // ---------------------------------------------------------------
    // STORECOVE
    // ---------------------------------------------------------------

    private function envoyerStorecove(Facture $facture, string $baseUrl, string $apiKey, string $entityId, ParametresEntreprise $params): array
    {
        $client = $facture->client;

        $invoiceData = [
            'invoiceNumber'       => $facture->numero,
            'issueDate'           => $facture->date_document->format('Y-m-d'),
            'dueDate'             => $facture->date_echeance?->format('Y-m-d'),
            'invoiceCurrencyCode' => 'EUR',
            'amountIncludingVat'  => (float) $facture->montant_ttc,
            'taxSystem'           => 'tax_line_percentages',

            'accountingSupplierParty' => [
                'publicIdentifiers' => [
                    [
                        'scheme' => 'BE:EN',
                        'id'     => $params->numero_tva ?? $params->peppol_id,
                    ],
                ],
            ],

            'accountingCustomerParty' => [
                'party' => [
                    'companyName' => $client->nom,
                    'address'     => [
                        'street1' => $client->adresse,
                        'zip'     => $client->code_postal,
                        'city'    => $client->ville,
                        'country' => $this->codePaysBelgique($client->pays),
                    ],
                    'publicIdentifiers' => $client->numero_tva ? [
                        ['scheme' => 'BE:EN', 'id' => $client->numero_tva],
                    ] : [],
                ],
            ],

            'invoiceLines' => $facture->lignes
                ->where('est_section', false)
                ->values()
                ->map(fn($l, $i) => [
                    'lineId'             => (string) ($i + 1),
                    'description'        => $l->designation . ($l->detail ? " — {$l->detail}" : ''),
                    'quantity'           => (float) $l->quantite,
                    'unitCode'           => $this->mapUniteUbl($l->unite ?? ''),
                    'amountExcludingVat' => (float) $l->montant_ht,
                    'itemPrice'          => (float) $l->prix_unitaire,
                    'tax'                => [
                        'percentage' => (float) $l->taux_tva,
                        'country'    => 'BE',
                    ],
                ])
                ->toArray(),
        ];

        // PDF en pièce jointe de courtoisie
        $totauxTva  = $this->documentService->calculerTotauxTva($facture->lignes);
        $parametres = $params;
        $pdfOutput  = Pdf::loadView('pdf.facture', compact('facture', 'parametres', 'totauxTva'))
            ->setPaper('a4', 'portrait')
            ->output();

        $payload = [
            'legalEntityId' => (int) $entityId,
            'invoice'       => $invoiceData,
            'attachments'   => [
                [
                    'filename'     => "facture-{$facture->numero}.pdf",
                    'document'     => base64_encode($pdfOutput),
                    'mimeType'     => 'application/pdf',
                    'primaryImage' => true,
                ],
            ],
        ];

        // Destination Peppol du client
        if ($client->numero_tva) {
            $payload['invoiceRecipient'] = [
                'publicIdentifiers' => [
                    ['scheme' => 'BE:EN', 'id' => $client->numero_tva],
                ],
            ];
        }
        if ($client->email) {
            $payload['invoiceRecipient']['emails'] = [$client->email];
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post("{$baseUrl}/document_submissions", $payload);

        if ($response->successful()) {
            $ref = $response->json('guid') ?? $response->json('id');
            return [
                'success'   => true,
                'message'   => "Facture {$facture->numero} envoyée via Peppol (Storecove).",
                'reference' => (string) $ref,
            ];
        }

        $erreur = $response->json('errors') ?? $response->body();
        return [
            'success'   => false,
            'message'   => "Erreur Storecove ({$response->status()}) : " . (is_array($erreur) ? json_encode($erreur) : $erreur),
            'reference' => null,
        ];
    }

    private function envoyerAvoirStorecove(Avoir $avoir, string $baseUrl, string $apiKey, string $entityId, ParametresEntreprise $params): array
    {
        $client = $avoir->client;

        $invoiceData = [
            'invoiceNumber'       => $avoir->numero,
            'issueDate'           => $avoir->date_document->format('Y-m-d'),
            'invoiceCurrencyCode' => 'EUR',
            'amountIncludingVat'  => -abs((float) $avoir->montant_ttc),
            'taxSystem'           => 'tax_line_percentages',
            'billingReference'    => $avoir->facture->numero,

            'accountingSupplierParty' => [
                'publicIdentifiers' => [
                    ['scheme' => 'BE:EN', 'id' => $params->numero_tva ?? $params->peppol_id],
                ],
            ],

            'accountingCustomerParty' => [
                'party' => [
                    'companyName' => $client->nom,
                    'address'     => [
                        'street1' => $client->adresse,
                        'zip'     => $client->code_postal,
                        'city'    => $client->ville,
                        'country' => $this->codePaysBelgique($client->pays),
                    ],
                    'publicIdentifiers' => $client->numero_tva ? [
                        ['scheme' => 'BE:EN', 'id' => $client->numero_tva],
                    ] : [],
                ],
            ],

            'invoiceLines' => [
                [
                    'lineId'             => '1',
                    'description'        => $avoir->motif,
                    'quantity'           => 1,
                    'unitCode'           => 'C62',
                    'amountExcludingVat' => -abs((float) $avoir->montant_ht),
                    'itemPrice'          => abs((float) $avoir->montant_ht),
                    'tax'                => [
                        'percentage' => (float) $avoir->taux_tva,
                        'country'    => 'BE',
                    ],
                ],
            ],
        ];

        $parametres = $params;
        $pdfOutput  = Pdf::loadView('pdf.avoir', compact('avoir', 'parametres'))
            ->setPaper('a4', 'portrait')
            ->output();

        $payload = [
            'legalEntityId' => (int) $entityId,
            'invoice'       => $invoiceData,
            'attachments'   => [
                [
                    'filename'     => "avoir-{$avoir->numero}.pdf",
                    'document'     => base64_encode($pdfOutput),
                    'mimeType'     => 'application/pdf',
                    'primaryImage' => true,
                ],
            ],
        ];

        if ($client->numero_tva) {
            $payload['invoiceRecipient'] = [
                'publicIdentifiers' => [
                    ['scheme' => 'BE:EN', 'id' => $client->numero_tva],
                ],
            ];
        }
        if ($client->email) {
            $payload['invoiceRecipient']['emails'] = [$client->email];
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post("{$baseUrl}/document_submissions", $payload);

        if ($response->successful()) {
            $ref = $response->json('guid') ?? $response->json('id');
            return [
                'success'   => true,
                'message'   => "Avoir {$avoir->numero} envoyé via Peppol.",
                'reference' => (string) $ref,
            ];
        }

        $erreur = $response->json('errors') ?? $response->body();
        return [
            'success'   => false,
            'message'   => "Erreur Storecove ({$response->status()}) : " . (is_array($erreur) ? json_encode($erreur) : $erreur),
            'reference' => null,
        ];
    }

    private function discoveryStorecove(string $baseUrl, string $apiKey, string $numeroTva): bool
    {
        $response = Http::withToken($apiKey)
            ->timeout(10)
            ->post("{$baseUrl}/discovery/receives", [
                'documentTypes' => ['invoice'],
                'network'       => 'peppol',
                'metaScheme'    => 'iso6523-actorid-upis',
                'scheme'        => '0208',
                'identifier'    => preg_replace('/[^0-9]/', '', $numeroTva),
            ]);

        return $response->successful() && ($response->json('code') === 'OK' || !empty($response->json()));
    }

    // ---------------------------------------------------------------
    // BILLIT
    // ---------------------------------------------------------------

    private function envoyerBillit(Facture $facture, string $baseUrl, string $apiKey, string $entityId, ParametresEntreprise $params): array
    {
        // TODO: Implémenter selon la documentation Billit API
        // https://www.billit.eu/en-int/peppol-access-point/connect-your-software-with-the-billit-peppol-access-point/
        return ['success' => false, 'message' => 'Provider Billit : implémentation en cours.', 'reference' => null];
    }

    private function discoveryBillit(string $baseUrl, string $apiKey, string $numeroTva): bool
    {
        return false; // TODO
    }

    // ---------------------------------------------------------------
    // E-INVOICE.BE
    // ---------------------------------------------------------------

    private function envoyerEinvoiceBe(Facture $facture, string $baseUrl, string $apiKey, string $entityId, ParametresEntreprise $params): array
    {
        // TODO: Implémenter selon la documentation e-invoice.be
        // SDK PHP disponible, pay-per-use à 0,25 €/facture
        return ['success' => false, 'message' => 'Provider e-invoice.be : implémentation en cours.', 'reference' => null];
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function codePaysBelgique(?string $pays): string
    {
        if (!$pays) {
            return 'BE';
        }
        $map = [
            'belgique' => 'BE', 'belgium' => 'BE', 'belgië' => 'BE',
            'france' => 'FR', 'luxembourg' => 'LU', 'pays-bas' => 'NL',
            'nederland' => 'NL', 'allemagne' => 'DE', 'deutschland' => 'DE',
        ];
        return $map[strtolower(trim($pays))] ?? strtoupper(substr($pays, 0, 2));
    }

    private function mapUniteUbl(string $unite): string
    {
        $map = [
            'pièce' => 'C62', 'piece' => 'C62', 'pce' => 'C62', 'u' => 'C62',
            'm²' => 'MTK', 'm2' => 'MTK',
            'm³' => 'MTQ', 'm3' => 'MTQ',
            'ml' => 'MTR', 'm' => 'MTR', 'mètre' => 'MTR',
            'kg' => 'KGM', 'kilo' => 'KGM',
            'l' => 'LTR', 'litre' => 'LTR',
            'h' => 'HUR', 'heure' => 'HUR', 'hr' => 'HUR',
            'jour' => 'DAY', 'j' => 'DAY',
            'forfait' => 'C62', 'ff' => 'C62', 'ens' => 'C62', 'ensemble' => 'C62',
            'tonne' => 'TNE', 't' => 'TNE',
        ];
        return $map[strtolower(trim($unite))] ?? 'C62';
    }
}
