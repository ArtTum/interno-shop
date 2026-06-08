<?php

namespace App\Libraries\Fedex;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class LabelStorage
{
    public static function save(string $base64Label, string|int $orderId, string $vendorName): string
    {
        if (base64_encode(base64_decode($base64Label, true)) !== $base64Label) {
            throw new \Exception("Invalid base64 string");
        }

        $pdfContent = base64_decode($base64Label);

        if ($pdfContent === false) {
            throw new \Exception("Base64 decode failed");
        }

        $filename = "label_{$orderId}_" . time() . ".pdf";
        $directory = "public/pdf/{$vendorName}/fedex";

        Storage::put("$directory/$filename", $pdfContent);

        Log::info('Fedex label saved at: ' . storage_path("app/$directory/$filename"));

        return $filename;
    }
}
