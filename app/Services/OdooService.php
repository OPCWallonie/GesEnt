<?php

namespace App\Services;

use App\Models\ParametresEntreprise;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OdooService
{
    private ?string $url      = null;
    private ?string $db       = null;
    private ?string $username = null;
    private ?string $apiKey   = null;
    private ?int    $uid      = null;

    public function __construct()
    {
        $params = ParametresEntreprise::instance();
        if ($params->odooActif()) {
            $this->url      = rtrim($params->odoo_url, '/');
            $this->db       = $params->odoo_database;
            $this->username = $params->odoo_username;
            $this->apiKey   = $params->odoo_api_key_decrypte;
        }
    }

    public function isConfigured(): bool
    {
        return (bool) ($this->url && $this->db && $this->username && $this->apiKey);
    }

    public function reconfigurer(string $url, string $db, string $username, string $apiKey): void
    {
        $this->url      = rtrim($url, '/');
        $this->db       = $db;
        $this->username = $username;
        $this->apiKey   = $apiKey;
        $this->uid      = null;
    }

    // ---------------------------------------------------------------
    // Authentification JSON-RPC
    // ---------------------------------------------------------------

    private function authenticate(): int
    {
        if ($this->uid) {
            return $this->uid;
        }

        $result = $this->jsonRpc("{$this->url}/jsonrpc", 'call', [
            'service' => 'common',
            'method'  => 'authenticate',
            'args'    => [$this->db, $this->username, $this->apiKey, []],
        ]);

        if (!$result || !is_int($result)) {
            throw new \RuntimeException(
                "Authentification Odoo échouée. Vérifiez l'URL, la base de données, le login et la clé API."
            );
        }

        $this->uid = $result;
        return $this->uid;
    }

    // ---------------------------------------------------------------
    // CRUD génériques
    // ---------------------------------------------------------------

    public function search(string $model, array $domain = [], array $options = []): array
    {
        $this->authenticate();
        return $this->execute($model, 'search', [$domain], $options) ?? [];
    }

    public function searchRead(string $model, array $domain = [], array $fields = [], array $options = []): array
    {
        $this->authenticate();
        $opts = array_merge($options, $fields ? ['fields' => $fields] : []);
        return $this->execute($model, 'search_read', [$domain], $opts) ?? [];
    }

    public function create(string $model, array $values): int
    {
        $this->authenticate();
        $result = $this->execute($model, 'create', [$values]);
        return is_array($result) ? ($result[0] ?? 0) : (int) $result;
    }

    public function write(string $model, int $id, array $values): bool
    {
        $this->authenticate();
        return (bool) $this->execute($model, 'write', [[$id], $values]);
    }

    public function read(string $model, array $ids, array $fields = []): array
    {
        $this->authenticate();
        return $this->execute($model, 'read', [$ids], $fields ? ['fields' => $fields] : []) ?? [];
    }

    // ---------------------------------------------------------------
    // Test de connexion
    // ---------------------------------------------------------------

    public function testerConnexion(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Odoo non configuré.'];
        }

        try {
            $uid = $this->authenticate();

            $version = $this->jsonRpc("{$this->url}/jsonrpc", 'call', [
                'service' => 'common',
                'method'  => 'version',
                'args'    => [],
            ]);

            $serverVersion = $version['server_version'] ?? 'inconnue';

            return [
                'success' => true,
                'uid'     => $uid,
                'version' => $serverVersion,
                'message' => "Connecté à Odoo {$serverVersion} (UID {$uid}).",
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------
    // JSON-RPC bas niveau
    // ---------------------------------------------------------------

    private function execute(string $model, string $method, array $args, array $kwargs = []): mixed
    {
        $uid = $this->authenticate();

        return $this->jsonRpc("{$this->url}/jsonrpc", 'call', [
            'service' => 'object',
            'method'  => 'execute_kw',
            'args'    => [$this->db, $uid, $this->apiKey, $model, $method, $args, $kwargs],
        ]);
    }

    private function jsonRpc(string $url, string $method, array $params): mixed
    {
        $response = Http::timeout(30)->post($url, [
            'jsonrpc' => '2.0',
            'method'  => $method,
            'params'  => $params,
            'id'      => random_int(1, 999999),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Odoo HTTP error {$response->status()} : {$response->body()}");
        }

        $data = $response->json();

        if (isset($data['error'])) {
            $msg = $data['error']['data']['message']
                ?? $data['error']['message']
                ?? json_encode($data['error']);
            Log::error('Odoo RPC error', ['error' => $msg, 'url' => $url]);
            throw new \RuntimeException("Erreur Odoo : {$msg}");
        }

        return $data['result'] ?? null;
    }
}
