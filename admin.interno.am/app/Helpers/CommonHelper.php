<?php

use App\Constants\PageConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

if (!function_exists('extractFormattedNumber')) {
    function extractFormattedNumber($string): ?string
    {
        $cleaned = preg_replace('/[^0-9.,]/', '', $string);

        $cleaned = str_replace(',', '.', $cleaned);

        $parts = explode('.', $cleaned, 2);
        $number = $parts[0];
        if (isset($parts[1])) {
            $decimal = preg_replace('/\D/', '', $parts[1]);
            $number .= '.' . $decimal;
        }

        return number_format((float)$number, 2, '.', '');
    }
}

if (!function_exists('fix_double_slashes')) {
    function fix_double_slashes($string)
    {
        if (substr($string, -2) === '//') {
            return substr($string, 0, -1); // Remove the last slash
        }

        return $string;
    }
}

if (!function_exists('prepare_pagination')) {
    function prepare_pagination(int $page, int $perPage, int $totalItems): array
    {
        return [
            'total_items' => $totalItems,
            'limit' => $perPage,
            'showing' => [
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $totalItems)
            ]
        ];
    }
}

if (!function_exists('determineFileType')) {
    function determineFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } else {
            return 'file';
        }
    }
}

if (!function_exists('setDBConnection')) {
    function setDBConnection(?string $vendorKey): true|JsonResponse
    {
        if (!config()->has("database.connections.{$vendorKey}")) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        DB::purge($vendorKey);
        DB::setDefaultConnection($vendorKey);

        return true;
    }
}

function removeAccents($text, $deep = true)
{
    if (is_array($text)) {
        $text = implode(' ', $text);
    }

    $text = str_replace(
        ['Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß', 'º'],
        ['Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss', ''],
        $text
    );

    if (!$deep) {
        return $text;
    }

    setlocale(LC_ALL, 'en_US.utf8');

    // Safely process the string with iconv
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    return $text;
}

if (!function_exists('getUserIP')) {
    function getUserIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

if (!function_exists('replace_h1_tags')) {
    function replace_h1_tags(string $description): string
    {
        $description = str_replace('<h1>', '<h2>', $description);

        return str_replace('<h1/>', '<h2/>', $description);
    }
}

if (!function_exists('validate_url_for_download_image')) {
    function validate_url_for_download_image(string $url): string
    {
        $parts = parse_url($url);

        if ($parts && isset($parts['path'])) {
            $pathSegments = explode('/', $parts['path']);
            $encodedPath = implode('/', array_map('rawurlencode', $pathSegments));

            $validUrl = $parts['scheme'] . '://' . $parts['host'] . $encodedPath;

            return filter_var($validUrl, FILTER_VALIDATE_URL);
        } else {
            return false;
        }
    }
}

if (!function_exists('parseHtmlToAccordionArray')) {
    function parseHtmlToAccordionArray(string $htmlContent): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($dom);

        // Select all h1, h2, h3 elements that are not .atm-title
        $headers = $xpath->query('//h1[not(contains(@class, "atm-title"))] | //h2[not(contains(@class, "atm-title"))] | //h3[not(contains(@class, "atm-title"))]');
        $accordion = [];

        foreach ($headers as $header) {
            $currentContent = '';
            $nextElement = $header->nextSibling;

            while ($nextElement && !in_array($nextElement->nodeName, ['h1', 'h2', 'h3'])) {
                if ($nextElement->nodeType === XML_ELEMENT_NODE || $nextElement->nodeType === XML_TEXT_NODE) {
                    $currentContent .= $dom->saveHTML($nextElement);
                }
                $nextElement = $nextElement->nextSibling;
            }

            $accordion[] = [
                'title' => $dom->saveHTML($header),
                'content' => $currentContent
            ];
        }

        return $accordion;
    }
}

if (!function_exists('return_real_ip_customer')) {
    function return_real_ip_customer($request)
    {
        return !empty(request()->server()['HTTP_X_REAL_IP']) ? request()->server()['HTTP_X_REAL_IP'] : $request->ip();
    }
}

if (!function_exists('to_date_at_the_end')) {
    function to_date_at_the_end($date)
    {
        if (!str_contains($date, ' 23:59:59')) {
            return "{$date} 23:59:59";
        }

        return $date;
    }
}

if (!function_exists('get_customer_country_code')) {
    function get_customer_country_code($ip)
    {
        $url = "http://ip-api.com/json/{$ip}";

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (!empty($data['countryCode'])) {
            return $data['countryCode'];
        }

        return false;
    }
}

if (!function_exists('generatePromoCode')) {
    function generatePromoCode($length = 4, $segments = 3, $separator = '-'): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $promoCode = [];

        for ($i = 0; $i < $segments; $i++) {
            $segment = '';
            for ($j = 0; $j < $length; $j++) {
                $segment .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $promoCode[] = $segment;
        }

        return implode($separator, $promoCode);
    }
}

if (!function_exists('clean_url_from_marketing_sources')) {
    function clean_url_from_marketing_sources(string $url): string
    {
        $keysToRemove = PageConstants::MARKETING_SOURCE_QUERY_KEYS;

        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['query'])) {
            return $url; // No query parameters, return original URL
        }

        parse_str($parsedUrl['query'], $queryParams);
        foreach ($keysToRemove as $key) {
            unset($queryParams[$key]);
        }

        $newQuery = http_build_query($queryParams);
        return $parsedUrl['path'] . ($newQuery ? '?' . $newQuery : '');
    }
}

if (!function_exists('clean_query_params_from_marketing_sources')) {
    function clean_query_params_from_marketing_sources(array $array): array
    {
        $keysToRemove = PageConstants::MARKETING_SOURCE_QUERY_KEYS;

        foreach ($keysToRemove as $key) {
            unset($array[$key]);
        }

        return $array;
    }
}

if (!function_exists('validate_pinterest_epik')) {
    function validate_pinterest_epik(string $string): bool
    {
        if (preg_match('/^[A-Za-z0-9\-_]+$/', $string)) {
            if (strlen($string) > 30 && strlen($string) < 150) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('extract_clear_number')) {
    function extract_clear_number(string|int $string): bool
    {
        $number = null;
        if (preg_match('/\d+/', $string, $matches)) {
            $number = $matches[0]; // this will be '1259093'
        }

        return $number;
    }
}
