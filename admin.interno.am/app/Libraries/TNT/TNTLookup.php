<?php

namespace App\Libraries\TNT;

use Exception;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;

class TNTLookup
{
    /* Version */
    const VERSION = '1.0';
    const API_URL = 'https://express.tnt.com/expressconnect/pricing/gettownpost';

    protected $userId;
    protected $password;

    protected $xml;

    protected $config;

    protected $data = [
        'appId' => 'IN',
        'appVersion' => self::VERSION,
        'townsearch' => [
            'country' => '',
            'town' => '',
            'postcode' => '',
        ],
    ];

    /**
     * TNTLookup constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->userId = $config['userId'];
        $this->password = $config['password'];
    }

    public function setCountry(string $country): void
    {
        $this->data['townsearch']['country'] = $country;
    }

    public function getCityByZip(string $zip)
    {
        if (!$zip) {
            return false;
        }

        $this->data['townsearch']['postcode'] = $zip;
        $this->data['townsearch']['town'] = '';
        $this->xml = ArrayToXml::convert($this->data, 'townSearchRequest', true, 'UTF-8');

        $result = $this->xmlToArray($this->sendRequest());

        return $result['searchResult']['searchTown'] ?? false;
    }

    public function getZipByCity(string $city)
    {
        if (!$city) {
            return false;
        }

        $this->data['townsearch']['postcode'] = '';
        $this->data['townsearch']['town'] = $city;
        $this->xml = ArrayToXml::convert($this->data, 'townSearchRequest', true, 'UTF-8');

        $result = $this->xmlToArray($this->sendRequest());

        if (!$result || !isset($result['searchResult']['searchTown'])) {
            return false;
        }

        return [
            'city' => $result['searchResult']['searchTown'],
            'zip_from' => $result['searchResult']['searchPCStartRange'],
            'zip_to' => $result['searchResult']['searchPCEndRange'],
        ];
    }

    /**
     * Send a request to the TNT API
     *
     * @return string|null
     * @throws Exception
     */
    private function sendRequest()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode("$this->userId:$this->password"),
        ])
            ->timeout(5000)
            ->asForm()
            ->post(self::API_URL, [
                'body' => trim($this->xml),
            ]);

        if ($response->failed()) {
            return false;
        }

        if ($response->status() !== 200) {
            return false;
        }

        return $response->body();
    }

    /**
     * Convert XML response to array
     *
     * @param string|null $xml
     * @return array|false
     */
    public function xmlToArray(?string $xml)
    {
        if (!$xml) {
            return false;
        }

        $result = simplexml_load_string($xml);

        return $result ? json_decode(json_encode($result), true) : false;
    }
}
