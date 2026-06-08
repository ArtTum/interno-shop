<?php

namespace App\Libraries\DPD;

use App\Constants\ShippingConstants;
use Exception;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapHeader;

class DPDShipmentClient
{
    const SHIP_WSDL = 'https://public-ws.dpd.com/services/ShipmentService/V4_4?wsdl';
    const SOAPHEADER_URL = 'http://dpd.com/common/service/types/Authentication/2.0';
    const TRACKING_URL = 'https://tracking.dpd.de/parcelstatus?locale=:lang&query=:awb';

    protected $storeOrderMessage = ShippingConstants::STORE_ORDER_MESSAGE;
    protected $trackingLanguage = null;

    protected $authorization;
    protected $environment;
    protected $airWayBills = [];
    protected $label = null;

    public function __construct($authorizationService, bool $wsdlCache = true)
    {
        $this->authorization = $authorizationService;
        $this->environment = [
            'wsdlCache' => $wsdlCache,
            'shipWsdl' => self::SHIP_WSDL,
        ];
        $this->storeOrderMessage['order']['generalShipmentData']['sendingDepot'] = $this->authorization['token']->depot;
    }

    public function getLabels(): string
    {
        return $this->label;
    }

    public function setTrackingLanguage($language): void
    {
        $this->trackingLanguage = $language;
    }

    public function setPredict(array $array): void
    {
        if (!isset($array['channel']) or !isset($array['value']) or !isset($array['language'])) {
            throw new Exception('Predict array is not complete');
        }

        switch (strtolower($array['channel'])) {
            case 'email':
                $array['channel'] = 1;
                if (!filter_var($array['value'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Predict email address not valid');
                }
                break;
            case 'telephone':
                $array['channel'] = 2;
                if (empty($array['value'])) {
                    throw new Exception('Predict value (telephone) empty');
                }
                break;
            case 'sms':
                $array['channel'] = 3;
                if (empty($array['value'])) {
                    throw new Exception('Predict value (sms) empty');
                }
                break;
            default:
                throw new Exception('Predict channel not allowed');
        }

        if (ctype_alpha($array['language']) && strlen($array['language']) === 2) {
            $array['language'] = strtoupper($array['language']);
        }
        $this->storeOrderMessage['order']['productAndServiceData']['predict'] = $array;
    }

    public function setGeneralShipmentData($array): void
    {
        if (isset($array['mpsWeight']) && $array['mpsWeight']) {
            $array['mpsWeight'] = round($array['mpsWeight'] * 100);
        }
        $this->storeOrderMessage['order']['generalShipmentData'] = array_merge($this->storeOrderMessage['order']['generalShipmentData'], $array);
    }

    public function setPrintOption(array $printOptions): void
    {
        $this->storeOrderMessage['printOptions']['printOption'] = $printOptions;
    }

    public function setSender(array $array): void
    {
        $array['customerNumber'] = $this->authorization['customerNumber'];
        $array['city'] = strtoupper($array['city']);
        $this->storeOrderMessage['order']['generalShipmentData']['sender'] = array_merge($this->storeOrderMessage['order']['generalShipmentData']['sender'], $array);
    }

    public function setReceiver(array $array): void
    {
        $this->storeOrderMessage['order']['generalShipmentData']['recipient'] = array_merge($this->storeOrderMessage['order']['generalShipmentData']['recipient'], $array);
    }

    public function setPickup(array $array): void
    {
        $requiredKeys = ['quantity', 'date', 'fromTime1', 'toTime1', 'collectionRequestAddress'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $array)) {
                throw new Exception("Pickup array is missing the '$key' key");
            }
        }

        $this->storeOrderMessage['order']['productAndServiceData']['pickup'] = $array;
    }

    public function addParcel($array): void
    {
        if (!isset($array['weight'])) {
            throw new Exception('Parcel array is not complete');
        }

        $array['weight'] = round($array['weight'] * 100);

        if (isset($array['height']) && isset($array['length']) && isset($array['width'])) {
            $volume = str_pad((string)ceil($array['length']), 3, '0', STR_PAD_LEFT);
            $volume .= str_pad((string)ceil($array['width']), 3, '0', STR_PAD_LEFT);
            $volume .= str_pad((string)ceil($array['height']), 3, '0', STR_PAD_LEFT);
            $array['volume'] = $volume;
        }

        $this->storeOrderMessage['order']['parcels'][] = $array;
    }

    public function setCustomsData(array $data): void
    {
        $this->storeOrderMessage['productAndServiceData']['international'] = $data;
    }

    public function submit(): void
    {
        if (isset($this->storeOrderMessage['order']['productAndServiceData']['predict'])) {
            $recipientCountry = strtoupper($this->storeOrderMessage['order']['generalShipmentData']['recipient']['country']);

            if (!in_array($recipientCountry, ShippingConstants::PREDICT_COUNTRIES)) {
                throw new Exception('Predict service not available for this destination');
            }
        }

        if (count($this->storeOrderMessage['order']['parcels']) === 0) {
            throw new Exception('Create at least 1 parcel');
        }

        $soapParams = $this->getSoapParams();
        $this->storeOrderMessage = $this->makeParamsUnique($this->storeOrderMessage);

        try {
            $client = new SoapClient($this->environment['shipWsdl'], $soapParams);
            $header = new SOAPHeader(self::SOAPHEADER_URL, 'authentication', $this->authorization['token']);
            $client->__setSoapHeaders($header);
            $response = $client->storeOrders($this->storeOrderMessage);

            if (isset($response->orderResult->shipmentResponses->faults)) {
                throw new Exception($response->orderResult->shipmentResponses->faults->message);
            }

            $this->label = $response->orderResult->output->content;
            unset($response->orderResult->output->content);
            $this->processParcelInformation($response);

        } catch (Exception $e) {
            throw new Exception('Error during order submission: ' . $e->getMessage());
        }
    }

    protected function getSoapParams(): array
    {
        return $this->environment['wsdlCache'] ? [
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'trace' => true,
            'connection_timeout' => 500000,
            'keep_alive' => false,
        ] : [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'exceptions' => true,
            'trace' => true,
            'connection_timeout' => 500000,
            'keep_alive' => false,
        ];
    }

    protected function processParcelInformation($response): void
    {
        $parcelInformation = $response->orderResult->shipmentResponses->parcelInformation;

        if (is_array($parcelInformation)) {
            foreach ($parcelInformation as $parcelResponse) {
                $this->airWayBills[] = $this->generateAirWayBill($parcelResponse->parcelLabelNumber);
            }
        } else {
            $this->airWayBills[] = $this->generateAirWayBill($parcelInformation->parcelLabelNumber);
        }
    }

    protected function generateAirWayBill($parcelLabelNumber): array
    {
        return [
            'airWayBill' => $parcelLabelNumber,
            'trackingLink' => strtr(self::TRACKING_URL, [
                ':awb' => $parcelLabelNumber,
                ':lang' => $this->trackingLanguage,
            ])
        ];
    }

    public function makeParamsUnique(array $params): array
    {
        for ($i = 0; $i < count($params['order']['parcels']); $i++) {
            if (isset($params['order']['parcels'][$i]['international']['commercialInvoiceConsignee']) && is_array($params['order']['parcels'][$i]['international']['commercialInvoiceConsignee'])) {
                $params['order']['parcels'][$i]['international']['commercialInvoiceConsignee']['unique'] = uniqid();
            }
            if (isset($params['order']['parcels'][$i]['international']['commercialInvoiceConsignor']) && is_array($params['order']['parcels'][$i]['international']['commercialInvoiceConsignor'])) {
                $params['order']['parcels'][$i]['international']['commercialInvoiceConsignor']['unique'] = uniqid();
            }
        }

        return $params;
    }

    public function getParcelResponses(): array
    {
        return $this->airWayBills;
    }

    public function setSaturdayDelivery($bool): void
    {
        $this->storeOrderMessage['order']['productAndServiceData']['saturdayDelivery'] = $bool;
    }

    public function getStoreOrderMessage(): array
    {
        return $this->storeOrderMessage;
    }
}
