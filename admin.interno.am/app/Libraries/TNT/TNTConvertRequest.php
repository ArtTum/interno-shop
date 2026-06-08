<?php


namespace App\Libraries\TNT;

use Carbon\Carbon;
use Error;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;

class TNTConvertRequest
{
    protected $tntURL = 'https://express.tnt.com/expresslabel/documentation/getlabel';

    protected $expressInfo;

    protected $account;

    protected $trackNum;

    public $xmlString;

    public function __construct($expressInfo, $trackingNum, $accountNum)
    {
        $this->expressInfo = $expressInfo;
        $this->trackNum = $trackingNum;
        $this->account = $accountNum;
    }

    protected function removeAccents($text, $deep = true)
    {
        $text = str_replace(['Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß'], [
            'Ae',
            'Oe',
            'Ue',
            'ae',
            'oe',
            'ue',
            'ss',
        ], $text);

        if (!$deep) {
            return $text;
        }

        setlocale(LC_ALL, 'en_US.utf8');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        return $text;
    }

    protected function setPackages(): array
    {
        $parcels = $this->expressInfo['parcels'];
        $packages = [];

        foreach ($parcels as $k => $v) {
            $packages[$k]['identifier']['_cdata'] = strval($k + 1);
            $packages[$k]['goodsDescription']['_cdata'] = isset($v['description']) ? substr($v['description'], 0, 29) : '...';
            $packages[$k]['pieceMeasurements']['length']['_cdata'] = strval($v['LENGTH']);
            $packages[$k]['pieceMeasurements']['width']['_cdata'] = strval($v['WIDTH']);
            $packages[$k]['pieceMeasurements']['height']['_cdata'] = strval($v['HEIGHT']);
            $packages[$k]['pieceMeasurements']['weight']['_cdata'] = strval($v['WEIGHT']);
            $packages[$k]['pieces']['sequenceNumbers']['_cdata'] = strval($k + 1);
            $packages[$k]['pieces']['pieceReference']['_cdata'] = $this->expressInfo['references']['order_number'];
        }

        return $packages;
    }

    protected function setXML()
    {
        $expressInfo = $this->expressInfo;
        $different_collection = @$expressInfo['collection_data']['different_collection_address'];
        $collection = $expressInfo['collection_data'];
        $sender = $expressInfo['ship_from'];
        $consignee = $expressInfo['ship_to'];

        if ($consignee['country'] === 'DE' && $sender['country'] === 'DE') {
            $domestic = true;
        } else {
            $domestic = false;
        }

        $collectionDate = (Carbon::now()->isWeekend() ? Carbon::now()
            ->addDays(2) : Carbon::now())->format('Y-m-d\TH:i:s');

        $arr = [
            'consignment' => [
                'consignmentIdentity' => [
                    'consignmentNumber' => [
                        '_cdata' => $this->trackNum,
                    ],
                    'customerReference' => [
                        '_cdata' => $expressInfo['references']['order_number'],
                    ],
                ],
                'collectionDateTime' => [
                    '_cdata' => strval($collectionDate),
                ],
                'sender' => [
                    'name' => [
                        '_cdata' => $different_collection ? $collection['company_name'] : $sender['company_name'],
                    ],
                    'addressLine1' => [
                        '_cdata' => $different_collection ? mb_substr($this->removeAccents($collection['street1']), 0, 29) : mb_substr($this->removeAccents($sender['street1']), 0, 29),
                    ],
                    'addressLine2' => [
                        '_cdata' => $different_collection ? $collection['street2'] : $sender['street2'],
                    ],
                    'town' => [
                        '_cdata' => $different_collection ? $collection['city'] : $sender['city'],
                    ],
                    'exactMatch' => [
                        '_cdata' => 'Y',
                    ],
                    'province' => [
                        '_cdata' => $sender['province'] ?? '',
                    ],
                    'postcode' => [
                        '_cdata' => $different_collection ? $collection['postal_code'] : $sender['postal_code'],
                    ],
                    'country' => [
                        '_cdata' => $different_collection ? $collection['country'] : $sender['country'],
                    ],
                ],
                'delivery' => [//收件人
                    'name' => [
                        '_cdata' => $consignee['name'],
                    ],
                    'addressLine1' => [
                        '_cdata' => substr($consignee['street1'], 0, 29),
                    ],
                    'addressLine2' => [
                        '_cdata' => $consignee['street2'] ?: '',
                    ],
                    'addressLine3' => [
                        '_cdata' => $consignee['street3'] ?? '',
                    ],
                    'town' => [
                        '_cdata' => $consignee['city'],
                    ],
                    'exactMatch' => [
                        '_cdata' => 'Y',
                    ],
                    'province' => [
                        '_cdata' => $consignee['province'] ?? '',
                    ],
                    'postcode' => [
                        '_cdata' => $consignee['postal_code'],
                    ],
                    'country' => [
                        '_cdata' => $consignee['country'],
                    ],
                ],
                'product' => [
                    'lineOfBusiness' => [
                        '_cdata' => $domestic ? '1' : '2',
                    ],
                    'groupId' => [
                        '_cdata' => '0',
                    ],
                    'subGroupId' => [
                        '_cdata' => '0',
                    ],
                    'id' => ($domestic ? 'EP' : 'EC'),
                    'type' => [
                        '_cdata' => 'N',
                    ],
                ],

                'account' => [
                    'accountNumber' => [
                        '_cdata' => $this->account,
                    ],
                    'accountCountry' => [
                        '_cdata' => 'DE',
                    ],
                ],
                'totalNumberOfPieces' => [
                    '_cdata' => strval(count($expressInfo['parcels'])),
                ],
                'pieceLine' => $this->setPackages(),
            ],
        ];

        if (isset($this->expressInfo['hazardous_data'])) {
            $arr['consignment']['product']['option'] = ['_cdata' => 'LQ'];
        }
        $this->xmlString = ArrayToXml::convert($arr, 'labelRequest', true, 'UTF-8');
    }

    public function getResult()
    {
        $this->setXML();
        if (!empty($this->xmlString)) {
            $different_collection = @$this->expressInfo['collection_data']['different_collection_address'];
            $collection = $this->expressInfo['collection_data'];
            $sender = $this->expressInfo['ship_from'];

            $getResponseXML = $this->httpRequest();
            $xml_obj = simplexml_load_string($getResponseXML);
            $xml_arr = (array)$xml_obj;
            if (isset($xml_arr['brokenRules']) && isset($xml_arr['brokenRules']->errorDescription)) {
                $error = (array)$xml_arr['brokenRules']->errorDescription;
                $error = $error[0];
                throw new Error($error);
            }
            if ($different_collection) {
                $xml_data = simplexml_load_string($getResponseXML);

                $xml_data->consignment->consignmentLabelData->sender->name = $sender['company_name'];
                $xml_data->consignment->consignmentLabelData->sender->addressLine1 = substr($sender['street1'], 0, 29);
                $xml_data->consignment->consignmentLabelData->sender->addressLine2 = $sender['street2'];
                if (isset($sender['street3']) && $sender['street3']) {
                    $xml_data->consignment->consignmentLabelData->sender->addressLine3 = $sender['street3'];
                }
                $xml_data->consignment->consignmentLabelData->sender->town = $sender['city'];
                $xml_data->consignment->consignmentLabelData->sender->postcode = $sender['postal_code'];
                $xml_data->consignment->consignmentLabelData->sender->country = $sender['country'];
                $getResponseXML = $xml_data->asXML();
            }
            $result = $this->getPdf($getResponseXML);

            return [$result];
        } else {
            throw new Error('Error:This xmlString[object] is not initialized!');
        }
    }

    private function getPdf($xml)
    {
        $url = 'https://express.tnt.com/expresswebservices-website/app/render.html';
        $data = [
            'responseXml' => $xml,
            'documentType' => 'routingLabel',
            'contentType' => 'pdf',
        ];

        $response = Http::withHeaders([
            'Content-type' => 'application/x-www-form-urlencoded',  // Content-type header for form data
            'referer' => 'https://express.tnt.com/expresswebservices-website/app/routinglabelrequest.html',  // Referer header
        ])
            ->timeout(5000)
            ->asForm()
            ->post($url, $data);

        if ($response->successful()) {
            return $response->body();
        } else {
            return "Error: " . $response->status() . ' - ' . $response->body();
        }
    }

    private function httpRequest()
    {
        $url = $this->tntURL;
        $xml = trim($this->xmlString);

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml',
            'Accept' => 'text/xml',
        ])
            ->timeout(5000)
            ->withBody($xml, 'text/xml')
            ->post($url);

        return $response->body();
    }
}
