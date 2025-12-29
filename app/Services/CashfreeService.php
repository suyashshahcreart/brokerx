<?php

namespace App\Services;

use RuntimeException;

class CashfreeService{
    protected string $env;
    protected string $appId;
    protected string $secretKey;
    protected string $apiVersion;
    protected int $timeout;
    protected ?string $customBaseUrl;

    public function __construct(){
        $this->env = config('cashfree.env', 'sandbox');
        $this->appId = (string) config('cashfree.app_id', '');
        $this->secretKey = (string) config('cashfree.secret_key', '');
        $this->apiVersion = (string) config('cashfree.api_version', '2023-08-01');
        $this->timeout = (int) config('cashfree.timeout', 30);
        $this->customBaseUrl = config('cashfree.base_url');
    }

    public function mode(): string{
        return $this->env === 'production' ? 'production' : 'sandbox';
    }

    public function createOrder(array $payload): array{
        return $this->request('POST', '/orders', $payload);
    }

    public function fetchOrder(string $orderId): array{
        return $this->request('GET', '/orders/' . urlencode($orderId));
    }

    public function request(string $method, string $endpoint, ?array $payload = null): array{
        if (empty($this->appId) || empty($this->secretKey)) {
            throw new RuntimeException('Cashfree credentials are not configured.');
        }

        $url = rtrim($this->baseUrl(), '/') . '/' . ltrim($endpoint, '/');
        $headers = [
            'Content-Type: application/json',
            'x-api-version: ' . $this->apiVersion,
            'x-client-id: ' . $this->appId,
            'x-client-secret: ' . $this->secretKey,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        if (!empty($payload)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseBody === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Cashfree cURL error: {$error}");
        }

        curl_close($ch);

        $decoded = json_decode($responseBody, true);

        return [
            'status_code' => $httpCode,
            'body' => $responseBody,
            'json' => $decoded,
        ];
    }

    protected function baseUrl(): string{
        if (!empty($this->customBaseUrl)) {
            return rtrim($this->customBaseUrl, '/');
        }

        return $this->mode() === 'production'
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }
}

