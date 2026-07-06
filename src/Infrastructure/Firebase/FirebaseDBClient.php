<?php

namespace App\Infrastructure\Firebase;

class FirebaseDBClient extends FirebaseClient
{
    private string $databaseUrl;
    private ?string $idToken = null;

    public function __construct(array $config)
    {
        $this->databaseUrl = rtrim($config['database_url'] ?? '', '/');
    }

    /**
     * Set Firebase ID Token untuk autentikasi request
     */
    public function setToken(?string $idToken): void
    {
        $this->idToken = $idToken;
    }

    /**
     * Membangun URL lengkap dengan parameter auth jika ada
     */
    private function buildUrl(string $path, array $queryParams = []): string
    {
        $path = '/' . ltrim($path, '/');
        $url = "{$this->databaseUrl}{$path}.json";

        if ($this->idToken) {
            $queryParams['auth'] = $this->idToken;
        }

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * GET data dari Realtime Database
     */
    public function get(string $path, array $queryParams = []): array
    {
        $url = $this->buildUrl($path, $queryParams);
        return $this->request($url, 'GET');
    }

    /**
     * PUT (Overwrite) data ke Realtime Database
     */
    public function set(string $path, array $data): array
    {
        $url = $this->buildUrl($path);
        return $this->request($url, 'PUT', $data);
    }

    /**
     * POST (Push/Append) data ke Realtime Database (menghasilkan auto-key)
     */
    public function push(string $path, array $data): array
    {
        $url = $this->buildUrl($path);
        return $this->request($url, 'POST', $data);
    }

    /**
     * PATCH (Update) data tertentu ke Realtime Database
     */
    public function update(string $path, array $data): array
    {
        $url = $this->buildUrl($path);
        return $this->request($url, 'PATCH', $data);
    }

    /**
     * DELETE data dari Realtime Database
     */
    public function delete(string $path): array
    {
        $url = $this->buildUrl($path);
        return $this->request($url, 'DELETE');
    }
}
