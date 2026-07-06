<?php

namespace App\Infrastructure\Firebase;

class FirebaseClient
{
    /**
     * Mengirim HTTP request ke Firebase API menggunakan cURL.
     */
    protected function request(string $url, string $method = 'GET', array $data = [], array $headers = []): array
    {
        $ch = curl_init();
        
        // Default headers
        $requestHeaders = array_merge([
            'Content-Type: application/json',
            'Accept: application/json'
        ], $headers);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Boleh di-enable di prod dengan CA bundle asli

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error,
                'code' => 500
            ];
        }

        $decodedResponse = json_decode($response, true) ?: [];

        if ($httpCode >= 400) {
            $errorMessage = $decodedResponse['error']['message'] ?? ($decodedResponse['error'] ?? 'Unknown error occurred');
            return [
                'success' => false,
                'error' => $errorMessage,
                'code' => $httpCode,
                'data' => $decodedResponse
            ];
        }

        return [
            'success' => true,
            'data' => $decodedResponse,
            'code' => $httpCode
        ];
    }
}
