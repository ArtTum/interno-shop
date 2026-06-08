<?php

namespace App\Libraries\Fedex;

use Illuminate\Support\Facades\Http;

class Authentication
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;

    public function __construct(string $clientId, string $clientSecret, string $baseUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = $baseUrl;
    }

    public function getAccessToken(): string
    {
        $response = Http::asForm()->post($this->baseUrl . '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->ok()) {
            throw new \Exception('FedEx OAuth error: ' . $response->body());
        }

        return $response->json()['access_token'];
    }
}
