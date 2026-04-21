<?php

namespace App\Services;

use App\Models\ParametresEntreprise;
use App\Services\Ia\LlmClientService;
use Illuminate\Http\UploadedFile;
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
    private const PROVIDERS = [
        'claude'  => ['nom' => 'Claude (Anthropic)',    'vision' => true,  'gratuit' => false, 'prix' => '~$0.001/page'],
        'openai'  => ['nom' => 'ChatGPT (OpenAI)',      'vision' => true,  'gratuit' => false, 'prix' => '~$0.002/page'],
        'gemini'  => ['nom' => 'Gemini (Google)',       'vision' => true,  'gratuit' => true,  'prix' => 'Gratuit (quota)'],
        'mistral' => ['nom' => 'Mistral AI',            'vision' => true,  'gratuit' => false, 'prix' => '~$0.0005/page'],
        'ollama'  => ['nom' => 'Ollama (local/gratuit)', 'vision' => true, 'gratuit' => true,  'prix' => 'Gratuit'],
    ];

    public function __construct(private LlmClientService $llmClient) {}

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

        if (! $parametres->aiConfiguree()) {
            throw new \RuntimeException("Aucune IA configurée. Allez dans Paramètres > Intelligence artificielle.");
        }

        $provider = $parametres->ai_provider;
        $estPdf   = in_array($fichier->getMimeType(), ['application/pdf', 'application/x-pdf']);

        if ($estPdf) {
            $texte = $this->extraireTextePdf($fichier->getPathname());
            if (strlen($texte) > 100) {
                return $this->extraireDepuisTexte($texte);
            }
            if (! in_array($provider, ['claude'])) {
                throw new \RuntimeException("Ce PDF est scanné (image). Utilisez Claude ou photographiez la facture pour les autres providers.");
            }
        }

        return $this->extraireDepuisImage($fichier, $estPdf);
    }

    private function extraireDepuisTexte(string $texte): array
    {
        $prompt   = $this->buildPromptTexte($texte);
        $resultat = $this->llmClient->appeler($prompt);
        return $this->parseReponse($resultat['contenu']);
    }

    private function extraireDepuisImage(UploadedFile $fichier, bool $estPdf): array
    {
        $prompt   = $this->buildPromptVision();
        $contenu  = file_get_contents($fichier->getPathname());
        $base64   = base64_encode($contenu);
        $mimeType = $estPdf ? 'application/pdf' : $fichier->getMimeType();

        $resultat = $this->llmClient->appeler($prompt, ['base64' => $base64, 'mime' => $mimeType]);
        return $this->parseReponse($resultat['contenu']);
    }

    private function extraireTextePdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseFile($path);
            return $pdf->getText();
        } catch (\Exception $e) {
            Log::warning('PDF text extraction failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

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

    private function parseReponse(string $reponse): array
    {
        $reponse = preg_replace('/^```json\s*/i', '', trim($reponse));
        $reponse = preg_replace('/\s*```$/', '', $reponse);

        $data = json_decode(trim($reponse), true);

        if (! $data) {
            Log::error('OCR parse failed', ['response' => $reponse]);
            throw new \RuntimeException("L'IA n'a pas retourné un JSON valide. Réessayez ou saisissez manuellement.");
        }

        $tva = (float) ($data['taux_tva_principal'] ?? 21);
        if ($tva <= 0) {
            $tva = 21;
        }

        return [
            'fournisseur_nom'    => $data['fournisseur_nom'] ?? null,
            'numero_facture'     => $data['numero_facture'] ?? null,
            'date_document'      => $data['date_document'] ?? now()->format('Y-m-d'),
            'date_echeance'      => $data['date_echeance'] ?? null,
            'montant_ht'         => (float) ($data['montant_ht'] ?? 0),
            'taux_tva'           => $tva,
            'montant_tva'        => (float) ($data['montant_tva'] ?? 0),
            'montant_ttc'        => (float) ($data['montant_ttc'] ?? 0),
            'reference_chantier' => isset($data['reference_chantier']) && $data['reference_chantier'] !== 'null'
                                    ? $data['reference_chantier']
                                    : null,
            'notes'              => $data['notes'] ?? null,
        ];
    }
}
