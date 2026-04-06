<?php

namespace App\Http\Controllers;

use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Models\ParametresEntreprise;
use App\Models\PeppolWebhookLog;
use App\Models\User;
use App\Notifications\NouvelleFactureAchatPeppol;
use App\Services\NumerotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeppolWebhookController extends Controller
{
    public function __construct(private NumerotationService $numerotation) {}

    public function handle(Request $request)
    {
        $params = ParametresEntreprise::instance();

        if (($params->peppol_mode ?? 'desactive') !== 'complet') {
            return response()->json(['error' => 'Peppol reception disabled'], 403);
        }

        $token         = $request->header('X-Peppol-Token') ?? $request->query('token');
        $expectedToken = $params->peppol_webhook_token;

        if (!$expectedToken || !hash_equals($expectedToken, $token ?? '')) {
            Log::warning('Peppol webhook: invalid token', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $provider = $params->peppol_provider;

        $log = PeppolWebhookLog::create([
            'provider'    => $provider,
            'event_type'  => $request->input('event_type', 'unknown'),
            'document_id' => $request->input('guid') ?? $request->input('id'),
            'status'      => 'received',
            'payload'     => $request->all(),
        ]);

        try {
            $result = match ($provider) {
                'storecove' => $this->handleStorecove($request, $params, $log),
                'billit'    => $this->handleBillit($request, $params, $log),
                default     => ['success' => false, 'message' => "Provider {$provider} non supporté en réception"],
            };

            $log->update([
                'status'           => $result['success'] ? 'processed' : 'failed',
                'error_message'    => $result['success'] ? null : $result['message'],
                'facture_achat_id' => $result['facture_achat_id'] ?? null,
            ]);

            return response()->json($result, $result['success'] ? 200 : 422);

        } catch (\Exception $e) {
            Log::error('Peppol webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    // ---------------------------------------------------------------
    // STORECOVE — réception
    // ---------------------------------------------------------------

    private function handleStorecove(Request $request, ParametresEntreprise $params, PeppolWebhookLog $log): array
    {
        $eventType = $request->input('event_type');

        if ($eventType !== 'received_document') {
            return ['success' => true, 'message' => "Event {$eventType} ignoré (non pertinent)"];
        }

        $guid = $request->input('guid') ?? $request->input('document_guid');
        if (!$guid) {
            return ['success' => false, 'message' => 'Pas de guid dans le webhook'];
        }

        if (FactureAchat::where('peppol_id', $guid)->exists()) {
            $log->update(['status' => 'duplicate']);
            return ['success' => true, 'message' => "Document {$guid} déjà traité (doublon)"];
        }

        $baseUrl = ($params->peppol_environment ?? 'sandbox') === 'sandbox'
            ? 'https://api-sandbox.storecove.com/api/v2'
            : 'https://api.storecove.com/api/v2';

        $response = Http::withToken($params->peppol_api_key_decrypte)
            ->timeout(30)
            ->get("{$baseUrl}/document_submissions/{$guid}");

        if (!$response->successful()) {
            return ['success' => false, 'message' => "Impossible de récupérer le document {$guid} : HTTP {$response->status()}"];
        }

        $document = $response->json();
        $invoice  = $document['invoice'] ?? $document;

        return $this->creerFactureAchatDepuisPeppol($invoice, $guid, $document);
    }

    // ---------------------------------------------------------------
    // BILLIT — réception (TODO)
    // ---------------------------------------------------------------

    private function handleBillit(Request $request, ParametresEntreprise $params, PeppolWebhookLog $log): array
    {
        return ['success' => false, 'message' => 'Réception Billit : implémentation en cours'];
    }

    // ---------------------------------------------------------------
    // Créer la FactureAchat depuis les données Peppol structurées
    // ---------------------------------------------------------------

    private function creerFactureAchatDepuisPeppol(array $invoice, string $peppolId, array $rawData): array
    {
        $supplierParty = $invoice['accountingSupplierParty']
            ?? $invoice['accounting_supplier_party']
            ?? [];
        $party = $supplierParty['party'] ?? $supplierParty;

        $supplierName = $party['companyName']
            ?? $party['company_name']
            ?? $party['registrationName']
            ?? $party['registration_name']
            ?? 'Fournisseur inconnu';

        $supplierTva      = null;
        $supplierPeppolId = null;
        foreach ($party['publicIdentifiers'] ?? $party['public_identifiers'] ?? [] as $id) {
            if (in_array($id['scheme'] ?? '', ['BE:EN', 'BE:CBE', '0208'])) {
                $supplierTva      = $id['id'] ?? null;
                $supplierPeppolId = ($id['scheme'] ?? '0208') . ':' . ($id['id'] ?? '');
            }
        }

        $fournisseur = $this->trouverOuCreerFournisseur($supplierName, $supplierTva, $supplierPeppolId, $party);

        $montantTtc = (float) ($invoice['amountIncludingVat'] ?? $invoice['amount_including_vat'] ?? 0);
        $lignes     = $invoice['invoiceLines'] ?? $invoice['invoice_lines'] ?? [];

        $montantHt       = 0;
        $tauxTvaPrincipal = 21;

        if (!empty($lignes)) {
            foreach ($lignes as $ligne) {
                $montantHt += (float) ($ligne['amountExcludingVat'] ?? $ligne['amount_excluding_vat'] ?? 0);
            }
            $tauxTvaPrincipal = (int) ($lignes[0]['tax']['percentage'] ?? $lignes[0]['tax_percentage'] ?? 21);
        } else {
            $montantHt = $montantTtc / (1 + $tauxTvaPrincipal / 100);
        }

        $montantTva = $montantTtc - $montantHt;

        $numeroFournisseur = $invoice['invoiceNumber'] ?? $invoice['invoice_number'] ?? $peppolId;
        $dateDocument      = $invoice['issueDate'] ?? $invoice['issue_date'] ?? now()->toDateString();
        $dateEcheance      = $invoice['dueDate'] ?? $invoice['due_date'] ?? null;

        // Anti-doublon par référence fournisseur
        $existant = FactureAchat::where('fournisseur_id', $fournisseur->id)
            ->where('reference_fournisseur', $numeroFournisseur)
            ->first();

        if ($existant) {
            return [
                'success'          => true,
                'message'          => "Facture {$numeroFournisseur} de {$fournisseur->nom} déjà enregistrée (doublon ref)",
                'facture_achat_id' => $existant->id,
            ];
        }

        $factureAchat = FactureAchat::create([
            'numero'                => $this->numerotation->suivant('facture_achat'),
            'fournisseur_id'        => $fournisseur->id,
            'reference_fournisseur' => $numeroFournisseur,
            'categorie'             => 'materiel',
            'date_document'         => $dateDocument,
            'date_echeance'         => $dateEcheance,
            'montant_ht'            => round($montantHt, 2),
            'taux_tva'              => $tauxTvaPrincipal,
            'montant_tva'           => round($montantTva, 2),
            'montant_ttc'           => round($montantTtc, 2),
            'statut'                => 'en_attente',
            'notes'                 => 'Reçue automatiquement via Peppol',
            'peppol_id'             => $peppolId,
            'peppol_sender_id'      => $supplierPeppolId,
            'peppol_recu_at'        => now(),
            'peppol_source'         => 'peppol',
            'peppol_raw_data'       => $rawData,
        ]);

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NouvelleFactureAchatPeppol($factureAchat));
        }

        return [
            'success'          => true,
            'message'          => "Facture {$numeroFournisseur} de {$fournisseur->nom} créée ({$factureAchat->numero})",
            'facture_achat_id' => $factureAchat->id,
        ];
    }

    // ---------------------------------------------------------------
    // Trouver ou créer un fournisseur depuis les données Peppol
    // ---------------------------------------------------------------

    private function trouverOuCreerFournisseur(string $nom, ?string $tva, ?string $peppolId, array $party): Fournisseur
    {
        if ($tva) {
            $f = Fournisseur::where('numero_tva', $tva)->first();
            if ($f) {
                if (!$f->peppol_id && $peppolId) $f->update(['peppol_id' => $peppolId]);
                return $f;
            }
        }

        if ($peppolId) {
            $f = Fournisseur::where('peppol_id', $peppolId)->first();
            if ($f) return $f;
        }

        $f = Fournisseur::where('nom', $nom)->first();
        if ($f) {
            $updates = [];
            if (!$f->numero_tva && $tva)    $updates['numero_tva'] = $tva;
            if (!$f->peppol_id && $peppolId) $updates['peppol_id'] = $peppolId;
            if ($updates) $f->update($updates);
            return $f;
        }

        $address = $party['address'] ?? [];
        return Fournisseur::create([
            'nom'         => $nom,
            'numero_tva'  => $tva,
            'peppol_id'   => $peppolId,
            'adresse'     => $address['street1'] ?? $address['street'] ?? null,
            'code_postal' => $address['zip'] ?? $address['postalCode'] ?? null,
            'ville'       => $address['city'] ?? null,
            'pays'        => $address['country'] ?? 'Belgique',
            'email'       => $party['contact']['email'] ?? $party['email'] ?? null,
            'telephone'   => $party['contact']['phone'] ?? $party['telephone'] ?? null,
            'actif'       => true,
            'notes'       => 'Créé automatiquement via Peppol',
        ]);
    }
}
