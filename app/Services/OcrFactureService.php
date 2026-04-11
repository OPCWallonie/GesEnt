<?php

namespace App\Services;

use App\Models\ParametresEntreprise;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Service d'extraction automatique de factures via IA.
 *
 * Providers supportés :
 *   claude   → Anthropic API (claude-haiku-4-5 par défaut, économique)
 *   openai   → OpenAI API   (gpt-4o-mini par défaut)
 *   gemini   → Google Gemini (gemini-1.5-flash, tier gratuit disponible)
 *   mistral  → Mistral AI   (pixtral-12b pour vision, mistral-small pour texte)
 *   ollama   → Local / auto-hébergé (llava ou llama3.2-vision)
 */
class OcrFactureService
{
    // Modèles par défaut (économiques) pour chaque provider
    private const MODELES_DEFAUT = [
        'claude'  => 'claude-haiku-4-5-20251001',
        'openai'  => 'gpt-4o-mini',
        'gemini'  => 'gemini-2.0-flash-lite',
        'mistral' => 'pixtral-12b-2409',
        'ollama'  => 'llava',
    ];

    private const PROVIDERS = [
        'claude'  => ['nom' => 'Claude (Anthropic)',   'vision' => true,  'gratuit' => false, 'prix' => '~$0.001/page'],
        'openai'  => ['nom' => 'ChatGPT (OpenAI)',     'vision' => true,  'gratuit' => false, 'prix' => '~$0.002/page'],
        'gemini'  => ['nom' => 'Gemini (Google)',      'vision' => true,  'gratuit' => true,  'prix' => 'Gratuit (quota)'],
        'mistral' => ['nom' => 'Mistral AI',           'vision' => true,  'gratuit' => false, 'prix' => '~$0.0005/page'],
        'ollama'  => ['nom' => 'Ollama (local/gratuit)','vision' => true, 'gratuit' => true,  'prix' => 'Gratuit'],
    ];

    public static function providers(): array
    {
        return self::PROVIDERS;
    }

    /**
     * Extraire les données d'une facture depuis un fichier PDF ou image.
     * Retourne un tableau structuré ou lance une exception.
     */
    public function extraire(UploadedFile $fichier): array
    {
        $parametres = ParametresEntreprise::instance();

        if (!$parametres->aiConfiguree()) {
            throw new \RuntimeException("Aucune IA configurée. Allez dans Paramètres > Intelligence artificielle.");
        }

        $provider = $parametres->ai_provider;
        $apiKey   = $parametres->ai_api_key_decrypte;
        $model    = $parametres->ai_model ?: (self::MODELES_DEFAUT[$provider] ?? '');
        $baseUrl  = $parametres->ai_url;

        // Détecter si c'est un PDF ou une image
        $estPdf = in_array($fichier->getMimeType(), ['application/pdf', 'application/x-pdf']);

        // Pour les PDFs : tenter d'abord l'extraction texte (plus économique, fonctionne sur tous les providers)
        if ($estPdf) {
            $texte = $this->extraireTextePdf($fichier->getPathname());
            if (strlen($texte) > 100) {
                return $this->extraireDepuisTexte($texte, $provider, $apiKey, $model, $baseUrl);
            }
            // PDF scanné → fallback vision (providers supportant base64 PDF)
            if (!in_array($provider, ['claude'])) {
                throw new \RuntimeException("Ce PDF est scanné (image). Utilisez Claude ou photographiez la facture pour les autres providers.");
            }
        }

        // Image ou PDF scanné → vision
        return $this->extraireDepuisImage($fichier, $estPdf, $provider, $apiKey, $model, $baseUrl);
    }

    // ---------------------------------------------------------------
    // Extraction depuis texte (PDF digital)
    // ---------------------------------------------------------------

    private function extraireDepuisTexte(string $texte, string $provider, ?string $apiKey, string $model, ?string $baseUrl): array
    {
        $prompt = $this->buildPromptTexte($texte);

        $reponse = match ($provider) {
            'claude'  => $this->appelClaude($prompt, null, $apiKey, $model),
            'openai'  => $this->appelOpenAI($prompt, null, $apiKey, $model),
            'gemini'  => $this->appelGemini($prompt, null, $apiKey, $model),
            'mistral' => $this->appelMistral($prompt, null, $apiKey, $model),
            'ollama'  => $this->appelOllama($prompt, null, $baseUrl, $model),
            default   => throw new \RuntimeException("Provider IA inconnu : {$provider}"),
        };

        return $this->parseReponse($reponse);
    }

    // ---------------------------------------------------------------
    // Extraction depuis image / PDF scanné (vision)
    // ---------------------------------------------------------------

    private function extraireDepuisImage(UploadedFile $fichier, bool $estPdf, string $provider, ?string $apiKey, string $model, ?string $baseUrl): array
    {
        $prompt    = $this->buildPromptVision();
        $contenu   = file_get_contents($fichier->getPathname());
        $base64    = base64_encode($contenu);
        $mimeType  = $estPdf ? 'application/pdf' : $fichier->getMimeType();

        $reponse = match ($provider) {
            'claude'  => $this->appelClaude($prompt, ['base64' => $base64, 'mime' => $mimeType], $apiKey, $model),
            'openai'  => $this->appelOpenAI($prompt, ['base64' => $base64, 'mime' => $mimeType], $apiKey, $model),
            'gemini'  => $this->appelGemini($prompt, ['base64' => $base64, 'mime' => $mimeType], $apiKey, $model),
            'mistral' => $this->appelMistral($prompt, ['base64' => $base64, 'mime' => $mimeType], $apiKey, $model),
            'ollama'  => $this->appelOllama($prompt, ['base64' => $base64, 'mime' => $mimeType], $baseUrl, $model),
            default   => throw new \RuntimeException("Provider IA inconnu : {$provider}"),
        };

        return $this->parseReponse($reponse);
    }

    // ---------------------------------------------------------------
    // Appels API par provider
    // ---------------------------------------------------------------

    private function appelClaude(string $prompt, ?array $image, ?string $apiKey, string $model): string
    {
        $content = [];

        if ($image) {
            $content[] = [
                'type'   => 'document',
                'source' => ['type' => 'base64', 'media_type' => $image['mime'], 'data' => $image['base64']],
            ];
        }
        $content[] = ['type' => 'text', 'text' => $prompt];

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $model,
            'max_tokens' => 1024,
            'messages'   => [['role' => 'user', 'content' => $content]],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Erreur Claude : " . ($response->json('error.message') ?? $response->status()));
        }

        return $response->json('content.0.text') ?? '';
    }

    private function appelOpenAI(string $prompt, ?array $image, ?string $apiKey, string $model): string
    {
        $content = [['type' => 'text', 'text' => $prompt]];

        if ($image) {
            $content[] = [
                'type'      => 'image_url',
                'image_url' => ['url' => "data:{$image['mime']};base64,{$image['base64']}"],
            ];
        }

        $response = Http::withToken($apiKey)->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => $model,
                'max_tokens' => 1024,
                'messages'   => [['role' => 'user', 'content' => $content]],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Erreur OpenAI : " . ($response->json('error.message') ?? $response->status()));
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    private function appelGemini(string $prompt, ?array $image, ?string $apiKey, string $model): string
    {
        $parts = [['text' => $prompt]];

        if ($image) {
            $parts[] = ['inline_data' => ['mime_type' => $image['mime'], 'data' => $image['base64']]];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(30)->post($url, [
            'contents' => [['parts' => $parts]],
        ]);

        if (!$response->successful()) {
            $msg = $response->json('error.message') ?? $response->status();
            if (str_contains((string)$msg, 'not found')) {
                $msg .= " — Vérifiez le nom du modèle dans Paramètres > IA. Modèles gratuits disponibles : gemini-2.0-flash-lite, gemini-2.0-flash.";
            }
            throw new \RuntimeException("Erreur Gemini : " . $msg);
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    private function appelMistral(string $prompt, ?array $image, ?string $apiKey, string $model): string
    {
        $content = [['type' => 'text', 'text' => $prompt]];

        if ($image) {
            $content[] = [
                'type'      => 'image_url',
                'image_url' => "data:{$image['mime']};base64,{$image['base64']}",
            ];
        }

        $response = Http::withToken($apiKey)->timeout(30)
            ->post('https://api.mistral.ai/v1/chat/completions', [
                'model'      => $image ? $model : 'mistral-small-latest',
                'max_tokens' => 1024,
                'messages'   => [['role' => 'user', 'content' => $content]],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Erreur Mistral : " . ($response->json('message') ?? $response->status()));
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    private function appelOllama(string $prompt, ?array $image, ?string $baseUrl, string $model): string
    {
        $url  = rtrim($baseUrl ?: 'http://localhost:11434', '/') . '/api/chat';
        $msg  = ['role' => 'user', 'content' => $prompt];

        if ($image) {
            $msg['images'] = [$image['base64']];
        }

        $response = Http::timeout(120)->post($url, [
            'model'    => $model,
            'messages' => [$msg],
            'stream'   => false,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Erreur Ollama : " . ($response->body() ?: $response->status()));
        }

        return $response->json('message.content') ?? '';
    }

    // ---------------------------------------------------------------
    // Extraction texte PDF
    // ---------------------------------------------------------------

    private function extraireTextePdf(string $path): string
    {
        try {
            $parser  = new PdfParser();
            $pdf     = $parser->parseFile($path);
            return $pdf->getText();
        } catch (\Exception $e) {
            Log::warning('PDF text extraction failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    // ---------------------------------------------------------------
    // Prompts
    // ---------------------------------------------------------------

    private function buildPromptTexte(string $texte): string
    {
        return <<<PROMPT
Voici le texte extrait d'une facture fournisseur. Extrais les informations et retourne UNIQUEMENT un objet JSON valide, sans texte avant ni après.

Texte de la facture :
---
{$texte}
---

JSON attendu :
{
  "fournisseur_nom": "nom du fournisseur",
  "numero_facture": "numéro de facture",
  "date_document": "YYYY-MM-DD",
  "date_echeance": "YYYY-MM-DD ou null",
  "montant_ht": 0.00,
  "taux_tva_principal": 21,
  "montant_tva": 0.00,
  "montant_ttc": 0.00,
  "reference_chantier": "code de référence chantier si présent dans la facture (format XX-YYYY-NNN ou code court similaire), sinon null",
  "notes": "référence commande ou autres infos utiles"
}

Règles : dates en YYYY-MM-DD, montants en nombre décimal sans symbole monétaire, null si information absente.
Pour reference_chantier : chercher dans la facture un code court alphanumérique avec tirets qui ressemble à une référence de chantier (ex: RBA-2026-001, VD-2026-003, CHT-001...). S'il n'y en a pas, mettre null.
PROMPT;
    }

    private function buildPromptVision(): string
    {
        return <<<PROMPT
Analyse cette facture fournisseur et retourne UNIQUEMENT un objet JSON valide, sans texte avant ni après.

JSON attendu :
{
  "fournisseur_nom": "nom du fournisseur",
  "numero_facture": "numéro de facture",
  "date_document": "YYYY-MM-DD",
  "date_echeance": "YYYY-MM-DD ou null",
  "montant_ht": 0.00,
  "taux_tva_principal": 21,
  "montant_tva": 0.00,
  "montant_ttc": 0.00,
  "reference_chantier": "code de référence chantier si présent dans la facture (format XX-YYYY-NNN ou code court similaire), sinon null",
  "notes": "référence commande ou autres infos utiles"
}

Règles : dates en YYYY-MM-DD, montants en nombre décimal sans symbole monétaire, null si information absente.
Pour reference_chantier : chercher dans la facture un code court alphanumérique avec tirets qui ressemble à une référence de chantier (ex: RBA-2026-001, VD-2026-003, CHT-001...). S'il n'y en a pas, mettre null.
PROMPT;
    }

    // ---------------------------------------------------------------
    // Parsing de la réponse JSON
    // ---------------------------------------------------------------

    private function parseReponse(string $reponse): array
    {
        // Nettoyer le markdown si présent (```json ... ```)
        $reponse = preg_replace('/^```json\s*/i', '', trim($reponse));
        $reponse = preg_replace('/\s*```$/', '', $reponse);

        $data = json_decode(trim($reponse), true);

        if (!$data) {
            Log::error('OCR parse failed', ['response' => $reponse]);
            throw new \RuntimeException("L'IA n'a pas retourné un JSON valide. Réessayez ou saisissez manuellement.");
        }

        // Normaliser et typer les valeurs
        $tva = (float) ($data['taux_tva_principal'] ?? 21);
        if ($tva <= 0) {
            $tva = 21;
        }

        return [
            'fournisseur_nom'    => $data['fournisseur_nom'] ?? null,
            'numero_facture'     => $data['numero_facture'] ?? null,
            'date_document'      => $data['date_document'] ?? now()->format('Y-m-d'),
            'date_echeance'      => $data['date_echeance'] ?? null,
            'montant_ht'         => (float)($data['montant_ht'] ?? 0),
            'taux_tva'           => $tva,
            'montant_tva'        => (float)($data['montant_tva'] ?? 0),
            'montant_ttc'        => (float)($data['montant_ttc'] ?? 0),
            'reference_chantier' => isset($data['reference_chantier']) && $data['reference_chantier'] !== 'null'
                                    ? $data['reference_chantier']
                                    : null,
            'notes'              => $data['notes'] ?? null,
        ];
    }
}
