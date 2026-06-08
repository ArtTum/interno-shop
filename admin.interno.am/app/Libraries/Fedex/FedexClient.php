<?php

namespace App\Libraries\Fedex;

use Illuminate\Support\Facades\Http;

class FedexClient
{
    protected string $token;
    protected string $baseUrl;

    public function __construct(string $token, string $baseUrl)
    {
        $this->token = $token;
        $this->baseUrl = $baseUrl;
    }

    public function createShipment(array $payload): array
    {
        $response = Http::withToken($this->token)->post($this->baseUrl . '/ship/v1/shipments', $payload);

        if (!$response->ok()) {
            throw new \Exception('FedEx REST error: ' . $response->body());
        }

        return $response->json();
    }
}

