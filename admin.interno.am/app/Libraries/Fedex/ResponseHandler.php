<?php

namespace App\Libraries\Fedex\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class ResponseHandler
{
    public static function handle(Response $response): array
    {
        if (!$response->ok()) {
            static::logError($response);
            throw new \Exception(static::extractErrorMessage($response));
        }

        $data = $response->json();

        if (!isset($data['output']['transactionShipments'][0])) {
            throw new \Exception('Unexpected FedEx API response structure.');
        }

        return [
            'tracking_number' => $data['output']['transactionShipments'][0]['masterTrackingNumber']['trackingNumber'] ?? null,
            'label_base64'    => $data['output']['transactionShipments'][0]['pieceResponses'][0]['packageDocuments'][0]['encodedLabel'] ?? null,
            'raw'             => $data,
        ];
    }

    protected static function extractErrorMessage(Response $response): string
    {
        $body = $response->json();

        if (isset($body['errors']) && is_array($body['errors'])) {
            return collect($body['errors'])->pluck('message')->implode(', ');
        }

        return 'FedEx API error: ' . $response->body();
    }

    protected static function logError(Response $response): void
    {
        Log::error('FedEx API Error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }
}
