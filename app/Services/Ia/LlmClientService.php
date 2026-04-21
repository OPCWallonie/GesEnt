<?php

namespace App\Services\Ia;

use App\Models\ParametresEntreprise;
use Illuminate\Support\Facades\Http;

class LlmClientService
{
    private const MODELES_DEFAUT = [
        'claude'  => 'claude-haiku-4-5-20251001',
        'openai'  => 'gpt-4o-mini',
        'gemini'  => 'gemini-2.0-flash-lite',
        'mistral' => 'pixtral-12b-2409',
        'ollama'  => 'llava',
    ];

    /**
     * Appelle le LLM configuré dans ParametresEntreprise.
     *
     * @param  array{base64: string, mime: string}|null $image
     * @return array{contenu: string, duree_ms: int, tokens_entree: ?int, tokens_sortie: ?int, modele: string, provider: string}
     */
    public function appeler(string $prompt, ?array $image = null): array
    {
        $params = ParametresEntreprise::instance();
        if (! $params->aiConfiguree()) {
            throw new \RuntimeException("Aucune IA configurée. Allez dans Paramètres > Intelligence artificielle.");
        }

        $provider = $params->ai_provider;
        $apiKey   = $params->ai_api_key_decrypte;
        $model    = $params->ai_model ?: (self::MODELES_DEFAUT[$provider] ?? '');
        $baseUrl  = $params->ai_url;

        $debut = microtime(true);

        $resultat = match ($provider) {
            'claude'  => $this->appelClaude($prompt, $image, $apiKey, $model),
            'openai'  => $this->appelOpenAI($prompt, $image, $apiKey, $model),
            'gemini'  => $this->appelGemini($prompt, $image, $apiKey, $model),
            'mistral' => $this->appelMistral($prompt, $image, $apiKey, $model),
            'ollama'  => $this->appelOllama($prompt, $image, $baseUrl, $model),
            default   => throw new \RuntimeException("Provider IA inconnu : {$provider}"),
        };

        $duree = (int) round((microtime(true) - $debut) * 1000);

        return [
            'contenu'       => $resultat['contenu'],
            'duree_ms'      => $duree,
            'tokens_entree' => $resultat['tokens_entree'] ?? null,
            'tokens_sortie' => $resultat['tokens_sortie'] ?? null,
            'modele'        => $model,
            'provider'      => $provider,
        ];
    }

    private function appelClaude(string $prompt, ?array $image, ?string $apiKey, string $model): array
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
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $model,
            'max_tokens' => 2048,
            'messages'   => [['role' => 'user', 'content' => $content]],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException($this->filtrerMessageErreur("Erreur Claude : " . ($response->json('error.message') ?? $response->status())));
        }

        return [
            'contenu'       => $response->json('content.0.text') ?? '',
            'tokens_entree' => $response->json('usage.input_tokens'),
            'tokens_sortie' => $response->json('usage.output_tokens'),
        ];
    }

    private function appelOpenAI(string $prompt, ?array $image, ?string $apiKey, string $model): array
    {
        $content = [['type' => 'text', 'text' => $prompt]];

        if ($image) {
            $content[] = [
                'type'      => 'image_url',
                'image_url' => ['url' => "data:{$image['mime']};base64,{$image['base64']}"],
            ];
        }

        $response = Http::withToken($apiKey)->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => $model,
                'max_tokens' => 2048,
                'messages'   => [['role' => 'user', 'content' => $content]],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException($this->filtrerMessageErreur("Erreur OpenAI : " . ($response->json('error.message') ?? $response->status())));
        }

        return [
            'contenu'       => $response->json('choices.0.message.content') ?? '',
            'tokens_entree' => $response->json('usage.prompt_tokens'),
            'tokens_sortie' => $response->json('usage.completion_tokens'),
        ];
    }

    private function appelGemini(string $prompt, ?array $image, ?string $apiKey, string $model): array
    {
        $parts = [['text' => $prompt]];

        if ($image) {
            $parts[] = ['inline_data' => ['mime_type' => $image['mime'], 'data' => $image['base64']]];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(60)->post($url, [
            'contents' => [['parts' => $parts]],
        ]);

        if (! $response->successful()) {
            $msg = $response->json('error.message') ?? $response->status();
            if (str_contains((string) $msg, 'not found')) {
                $msg .= " — Vérifiez le nom du modèle dans Paramètres > IA. Modèles gratuits disponibles : gemini-2.0-flash-lite, gemini-2.0-flash.";
            }
            throw new \RuntimeException($this->filtrerMessageErreur("Erreur Gemini : " . $msg));
        }

        return [
            'contenu'       => $response->json('candidates.0.content.parts.0.text') ?? '',
            'tokens_entree' => $response->json('usageMetadata.promptTokenCount'),
            'tokens_sortie' => $response->json('usageMetadata.candidatesTokenCount'),
        ];
    }

    private function appelMistral(string $prompt, ?array $image, ?string $apiKey, string $model): array
    {
        $content = [['type' => 'text', 'text' => $prompt]];

        if ($image) {
            $content[] = [
                'type'      => 'image_url',
                'image_url' => "data:{$image['mime']};base64,{$image['base64']}",
            ];
        }

        $response = Http::withToken($apiKey)->timeout(60)
            ->post('https://api.mistral.ai/v1/chat/completions', [
                'model'      => $image ? $model : 'mistral-small-latest',
                'max_tokens' => 2048,
                'messages'   => [['role' => 'user', 'content' => $content]],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException($this->filtrerMessageErreur("Erreur Mistral : " . ($response->json('message') ?? $response->status())));
        }

        return [
            'contenu'       => $response->json('choices.0.message.content') ?? '',
            'tokens_entree' => $response->json('usage.prompt_tokens'),
            'tokens_sortie' => $response->json('usage.completion_tokens'),
        ];
    }

    private function appelOllama(string $prompt, ?array $image, ?string $baseUrl, string $model): array
    {
        $url = rtrim($baseUrl ?: 'http://localhost:11434', '/') . '/api/chat';
        $msg = ['role' => 'user', 'content' => $prompt];

        if ($image) {
            $msg['images'] = [$image['base64']];
        }

        $response = Http::timeout(120)->post($url, [
            'model'    => $model,
            'messages' => [$msg],
            'stream'   => false,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException($this->filtrerMessageErreur("Erreur Ollama : " . ($response->body() ?: $response->status())));
        }

        return [
            'contenu'       => $response->json('message.content') ?? '',
            'tokens_entree' => null,
            'tokens_sortie' => null,
        ];
    }

    private function filtrerMessageErreur(string $message): string
    {
        $patterns = ['sk-', 'AIza', 'Bearer ', 'api_key=', 'key='];
        foreach ($patterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return 'Erreur de connexion au service IA. Vérifiez votre clé API dans les paramètres.';
            }
        }
        return $message;
    }
}
