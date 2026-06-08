<?php

namespace App\Libraries\TNT;

use Exception;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;

class TNTShipService
{
    /* Version */
    const VERSION = '3.0';

    /* Service URL Of Express Connect */
    const CONNECTURL = 'https://express.tnt.com/expressconnect/shipping/ship';
    const SERVICE = '48N';

    /**
     * User ID
     * @var string
     */
    protected $userId;

    /**
     * Password
     * @var string
     */
    protected $password;

    /**
     * Account number
     * @var int
     */
    protected $account = 0;

    protected $xml;

    /**
     * Request information
     *
     * @var array|null
     */
    protected $expressInfo;


    /**
     * Response HTTP headers
     * @var array
     */
    static public $headers = [];

    protected $credentials;

    /**
     * TNTShipService constructor.
     *
     * @param array $express
     */
    public function __construct(array $express)
    {
        $this->expressInfo = $express;
        $this->setAuthentication();

        $this->setXml();
    }

    /**
     * @return array
     */
    public function setAuthentication(): array
    {
        $this->account = $this->expressInfo['account_number'];

        $login = [
            'COMPANY' => $this->expressInfo['userId'],
            'PASSWORD' => $this->expressInfo['password'],
            'APPID' => 'IN',
            'APPVERSION' => self::VERSION,
        ];

        return $login;
    }

    public function setSender(): array
    {
        $senderInfo = $this->expressInfo['ship_from'];
        $ci = $this->expressInfo['collection_data'];

        $sender = [
            'COMPANYNAME' => [
                '_cdata' => (@$ci['different_collection_address']) ? $ci['company_name'] : $senderInfo['company_name'],
            ],
            'STREETADDRESS1' => [
                '_cdata' => (@$ci['different_collection_address']) ? $ci['street1'] : $senderInfo['street1'],
            ],
            'STREETADDRESS2' => [
                '_cdata' => (@$ci['different_collection_address']) ? $ci['street2'] : $senderInfo['street2'],
            ],
            'CITY' => [
                '_cdata' => (@$ci['different_collection_address']) ? $ci['city'] : $senderInfo['city'],
            ],
            'POSTCODE' => [
                '_cdata' => (@$ci['different_collection_address']) ? $ci['postal_code'] : $senderInfo['postal_code'],
            ],
            'COUNTRY' => [
                '_cdata' => (@$ci['different_collection_address']) ? $ci['country'] : $senderInfo['country'],
            ],
            'ACCOUNT' => [
                '_cdata' => $this->account,
            ],
            'CONTACTNAME' => [
                '_cdata' => $senderInfo['name'],
            ],
            'CONTACTDIALCODE' => [
                '_cdata' => substr($senderInfo['phone'], 0, -9),
            ],
            'CONTACTTELEPHONE' => [
                '_cdata' => substr($senderInfo['phone'], -9),
            ],
            'CONTACTEMAIL' => [
                '_cdata' => $senderInfo['email'],
            ],
            'COLLECTION' => $this->setCollection(),
        ];

        return $sender;
    }

    public function setCollection(): array
    {
        $collect = [
            'COLLECTIONADDRESS' => array(),
            'SHIPDATE' => $this->expressInfo['pickup']['date'],
            'PREFCOLLECTTIME' => [
                'FROM' => [
                    '_cdata' => $this->expressInfo['pickup']['time_from'],
                ],
                'TO' => [
                    '_cdata' => $this->expressInfo['pickup']['time_to'],
                ],
            ],
            'COLLINSTRUCTIONS' => '',
        ];

        if (@$this->expressInfo['collection_data']['different_collection_address']) {
            $ci = $this->expressInfo['collection_data'];
            $collect ['COLLECTIONADDRESS'] = [
                'COMPANYNAME' => [
                    '_cdata' => $ci['company_name'],
                ],
                'STREETADDRESS1' => [
                    '_cdata' => $ci['street1'],
                ],
                'STREETADDRESS2' => [
                    '_cdata' => $ci['street2'],
                ],
                'CITY' => [
                    '_cdata' => $ci['city'],
                ],
                'POSTCODE' => [
                    '_cdata' => $ci['postal_code'],
                ],
                'COUNTRY' => [
                    '_cdata' => $ci['country'],
                ],
                'CONTACTNAME' => [
                    '_cdata' => $ci['name'],
                ],
                'CONTACTDIALCODE' => [
                    '_cdata' => substr($ci['phone'], 0, 3),
                ],
                'CONTACTTELEPHONE' => [
                    '_cdata' => substr($ci['phone'], 3),
                ],
                'CONTACTEMAIL' => [
                    '_cdata' => $ci['email'],
                ],
            ];
        } else {
            unset($collect['COLLECTIONADDRESS']);
        }

        return $collect;
    }

    public function setReceiver(): array
    {
        if ($this->expressInfo['receiver_address_type'] === 'client') {
            $receiver = $this->setDelivery();
        } else {
            $receiver = $this->setSender();
            unset($receiver['ACCOUNT']);
            unset($receiver['COLLECTION']);
        }

        return $receiver;
    }

    public function setConNumber()
    {
        $consignmentNumber = $this->expressInfo['consignment_number'];
        unset($this->expressInfo['consignment_number']);
        return $consignmentNumber;
    }

    /**
     * Delivery address same as receiver
     * @return array
     */
    public function setDelivery(): array
    {
        $deliveryInfo = $this->expressInfo['ship_to'];
        $dialcode = substr($deliveryInfo['phone'], 0, -9);
        if (!$dialcode) {
            $dialcode = '0';
        }
        $phone = substr($deliveryInfo['phone'], -9);
        if (!$phone) {
            $phone = '0';
        }

        $delivery = [
            'COMPANYNAME' => [
                '_cdata' => $deliveryInfo['company_name'] ?: $deliveryInfo['name'],
            ],
            'STREETADDRESS1' => [
                '_cdata' => $deliveryInfo['street1'],
            ],
            'STREETADDRESS2' => [
                '_cdata' => $deliveryInfo['street2'],
            ],
            'STREETADDRESS3' => [
                '_cdata' => $deliveryInfo['street3'] ?? '',
            ],
            'CITY' => [
                '_cdata' => $deliveryInfo['city'],
            ],
            'POSTCODE' => [
                '_cdata' => $deliveryInfo['postal_code'],
            ],
            'COUNTRY' => [
                '_cdata' => $deliveryInfo['country'],
            ],
            'CONTACTNAME' => [
                '_cdata' => $deliveryInfo['name'],
            ],
            'CONTACTDIALCODE' => [
                '_cdata' => $dialcode,
            ],
            'CONTACTTELEPHONE' => [
                '_cdata' => $phone,
            ],
            'CONTACTEMAIL' => [
                '_cdata' => $deliveryInfo['email'],
            ],
        ];

        return $delivery;
    }

    /**
     * @return array
     */
    public function setCustomerRef(): array
    {
        return ['_cdata' => (string)$this->expressInfo['references']['order_number']];
    }

    /**
     * MUST be 'N' (Non-Doc) or 'D' (Doc)
     *
     * @param string $type
     *
     * @return array
     */
    public function setContype(string $type): array
    {
        switch ($type) {
            case 'Non-Doc':
                $conType = 'N';
                break;
            case 'Doc':
                $conType = 'D';
                break;
            default:
                $conType = 'N';
        }

        return ['_cdata' => $conType];
    }

    /**
     * MUST be 'S'(sender pays) or 'R'(receiver pays)
     *
     * @param string $pays
     *
     * @return array
     */
    public function setPaymentind(string $pays): array
    {
        switch ($pays) {
            case 'Sender':
                $paymentIndicator = 'S';
                break;
            case 'Receiver':
                $paymentIndicator = 'R';
                break;
            default:
                $paymentIndicator = 'S';
        }

        return ['_cdata' => $paymentIndicator];
    }

    /**
     * @return array
     */
    public function setItems(): array
    {
        return ['_cdata' => (string)count($this->expressInfo['parcels'])];
    }

    /**
     * @return array
     */
    public function setTotalWeight(): array
    {
        $totalWeight = 0;
        $parcels = $this->expressInfo['parcels'];
        foreach ($parcels as $parcel) {
            $parcel = $this->formatParcelUnit($parcel);
            $totalWeight += $parcel['WEIGHT'];
        }

        return ['_cdata' => (string)$totalWeight];
    }

    /**
     * @return array
     */
    public function setTotalVolume(): array
    {
        return ['_cdata' => '1.00'];
    }

    public function setService(): array
    {

        return ['_cdata' => self::SERVICE];
    }

    /**
     * @param array $parcel
     *
     * @return array
     */
    public function formatParcelUnit(array $parcel)
    {
        $sizeRate = 0.393700787;
        $weightRate = 2.2;
        if (isset($parcel['weight']) && strtolower($parcel['weight']['unit']) === 'lb') {
            $parcel['weight']['value'] = ceil($parcel['weight']['value'] / $weightRate);
        }
        if (isset($parcel['dimension']) && strtolower($parcel['dimension']['unit']) === 'in') {
            $parcel['dimension']['height'] = $parcel['dimension']['height'] / $sizeRate;
            $parcel['dimension']['width'] = $parcel['dimension']['width'] / $sizeRate;
            $parcel['dimension']['length'] = $parcel['dimension']['length'] / $sizeRate;
        }
        $parcel['weight']['unit'] = 'kg';
        $parcel['dimension']['unit'] = 'cm';

        return $parcel;
    }

    /**
     * @return array
     */
    public function setDescription(): array
    {
        return ['_cdata' => (string)$this->expressInfo['description']];
    }

    /**
     * @return array
     */
    public function setDeliveryInstructions(): array
    {
        return ['_cdata' => (string)$this->expressInfo['delivery_instructions']];
    }

    /**
     * TNT allows for maximum 50 packages per consignment.
     * @return array
     */
    public function setPackages(): array
    {
        $parcels = $this->expressInfo['parcels'];
        $package = [];
        foreach ($parcels as $key => $parcel) {
            $package[$key]['ITEMS'] = 1;
            $package[$key]['DESCRIPTION']['_cdata'] = strval($key + 1);
            $package[$key]['LENGTH']['_cdata'] = strval(round($parcel['LENGTH'], 4));
            $package[$key]['HEIGHT']['_cdata'] = strval(round($parcel['HEIGHT'], 4));
            $package[$key]['WIDTH']['_cdata'] = strval(round($parcel['WIDTH'], 4));
            $package[$key]['WEIGHT']['_cdata'] = strval($parcel['WEIGHT']);
        }

        return $package;
    }

    /**
     * @return array
     */
    public function setActivity(): array
    {
        $ref = (string)$this->expressInfo['references']['order_number'];
        $activity = [
            'CREATE' => ['CONREF' => ['_cdata' => $ref]],
            /**
             * This sends signal to TNT
             */
            'SHIP' => ['CONREF' => ['_cdata' => $ref]],
            'PRINT' => ['REQUIRED' => ['CONREF' => ['_cdata' => $ref]]],
        ];

        return $activity;
    }

    /**
     * @return $this
     */
    public function setXml()
    {
        $array = [
            'LOGIN' => $this->setAuthentication(),
            'CONSIGNMENTBATCH' => [
                'SENDER' => $this->setSender(),
                'CONSIGNMENT' => [
                    'CONREF' => strval($this->expressInfo['references']['order_number']),
                    'DETAILS' => [
                        'RECEIVER' => $this->setReceiver(),
                        'DELIVERY' => $this->setDelivery(),
                        'CONNUMBER' => $this->setConNumber(),
                        'CUSTOMERREF' => $this->setCustomerRef(),
                        'CONTYPE' => $this->setContype('Non-Doc'),
                        'PAYMENTIND' => $this->setPaymentind('Sender'),
                        'ITEMS' => $this->setItems(),
                        'TOTALWEIGHT' => $this->setTotalWeight(),
                        'TOTALVOLUME' => $this->setTotalVolume(),
                        'CURRENCY' => ['_cdata' => $this->expressInfo['totals']['currency']],
                        'GOODSVALUE' => ['_cdata' => $this->expressInfo['totals']['total']],
                        'SERVICE' => $this->setService(),
                        'OPTION' => '',
                        'DESCRIPTION' => $this->setDescription(),
                        'DELIVERYINST' => $this->setDeliveryInstructions(),
                        'HAZARDOUS' => ['_cdata' => 'N'],
                        'UNNUMBER' => '',
                        'PACKINGGROUP' => '',
                        'PACKAGE' => $this->setPackages(),
                    ],
                ],
            ],
            'ACTIVITY' => $this->setActivity(),
        ];
        if (@$this->expressInfo['incoterm']) {
            $array['CONSIGNMENTBATCH']['CONSIGNMENT']['DETAILS']['INCOTERMS'] = ['_cdata' => $this->expressInfo['incoterm']];
        }
        if (isset($this->expressInfo['hazardous_data'])) {
            $array['CONSIGNMENTBATCH']['CONSIGNMENT']['DETAILS']['HAZARDOUS']['_cdata'] = 'Y';
            $array['CONSIGNMENTBATCH']['CONSIGNMENT']['DETAILS']['UNNUMBER'] = ['_cdata' => $this->expressInfo['hazardous_data']['un_number']];
            $array['CONSIGNMENTBATCH']['CONSIGNMENT']['DETAILS']['PACKINGGROUP'] = ['_cdata' => $this->expressInfo['hazardous_data']['packing_groups']];
            $array['CONSIGNMENTBATCH']['CONSIGNMENT']['DETAILS']['OPTION'] = ['_cdata' => 'LQ'];
        }
        $this->xml = ArrayToXml::convert($array, 'ESHIPPER', true, 'UTF-8');
    }

    public function send(): ?array
    {
        $res = $this->sendRequest();

        $complete = explode(':', $res);

        if (isset($complete[0]) && $complete[0] === 'COMPLETE') {
            $this->xml = 'GET_RESULT:' . (int)$complete[1];
            $result = $this->sendRequest(true);

            if (!strpos($result, 'SUCCESS')) {

                $res = simplexml_load_string($result);

                $res = json_encode($res);
                $res = json_decode($res, true);

                if (isset($res['error_reason'])) {
                    throw new Exception($res['error_reason']);
                }
                $errors = array_column($res['ERROR'], 'DESCRIPTION');
                if (!$errors) {
                    $errors = [$res['ERROR']['DESCRIPTION']];
                }
                throw new Exception(implode('; ', $errors));
            }

            $xml = simplexml_load_string(trim($this->sendRequest()));
            $trackingNumber = preg_replace("/[^0-9]/", "", (string)$xml->CREATE->CONNUMBER);
            $pdfResult = new TNTConvertRequest($this->expressInfo, $trackingNumber, $this->account);

            $pdfString = $pdfResult->getResult();

            $res = [];
            foreach ($pdfString as $k => $pdf) {
                $res[$k]['tracking_number'] = $trackingNumber;
                $res[$k]['pdf'] = $pdf;
            }

            return $res;
        }

        throw new Exception('Fatal Error: ' . $res);
    }

    private function sendRequest()
    {
        $response = Http::withHeaders([
            "Content-Type" => "application/x-www-form-urlencoded",
            "Authorization" => "Basic " . base64_encode($this->expressInfo['userId'] . ':' . $this->expressInfo['password']),
        ])
            ->timeout(5000)
            ->asForm()
            ->post(self::CONNECTURL, [
                'xml_in' => $this->xml,
            ]);

        $body = $response->body();

        return $body;
    }
}
