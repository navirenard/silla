<?php

namespace App\Infrastructure\Firebase;

class FirebaseAuthClient extends FirebaseClient
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = $config['auth_url'] ?? 'https://identitytoolkit.googleapis.com/v1';
    }

    /**
     * Mendaftarkan pengguna baru dengan email dan password.
     */
    public function signUp(string $email, string $password): array
    {
        $url = "{$this->baseUrl}/accounts:signUp?key={$this->apiKey}";
        $data = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ];

        return $this->request($url, 'POST', $data);
    }

    /**
     * Login dengan email dan password.
     */
    public function signIn(string $email, string $password): array
    {
        $url = "{$this->baseUrl}/accounts:signInWithPassword?key={$this->apiKey}";
        $data = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ];

        return $this->request($url, 'POST', $data);
    }

    /**
     * Dapatkan detail user berdasarkan ID Token.
     */
    public function getUser(string $idToken): array
    {
        $url = "{$this->baseUrl}/accounts:lookup?key={$this->apiKey}";
        $data = [
            'idToken' => $idToken
        ];

        $response = $this->request($url, 'POST', $data);
        if ($response['success'] && isset($response['data']['users'][0])) {
            return [
                'success' => true,
                'user' => $response['data']['users'][0]
            ];
        }

        return [
            'success' => false,
            'error' => $response['error'] ?? 'User not found'
        ];
    }
}
