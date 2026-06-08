<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Soap\ClientDecorator;

use App\Libraries\dhl\Api\Data\AuthenticationStorageInterface;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponse;
use App\Libraries\dhl\Soap\AbstractClient;
use App\Libraries\dhl\Soap\AbstractDecorator;

class AuthenticationDecorator extends AbstractDecorator
{
    /**
     * @var \SoapClient
     */
    private $soapClient;

    /**
     * @var \App\Libraries\dhl\Api\Data\AuthenticationStorageInterface
     */
    private $authStorage;

    public function __construct(
        AbstractClient $client,
        \SoapClient $soapClient,
        AuthenticationStorageInterface $authStorage
    ) {
        $this->soapClient = $soapClient;
        $this->authStorage = $authStorage;

        parent::__construct($client);
    }

    private function addAuthHeader(): void
    {
        $authHeader = new \SoapHeader(
            'http://dhl.de/webservice/cisbase',
            'Authentification',
            [
                'user' => $this->authStorage->getUser(),
                'signature' => $this->authStorage->getSignature(),
            ]
        );

        $this->soapClient->__setSoapHeaders([$authHeader]);
    }

    public function validateShipment(ValidateShipmentOrderRequest $requestType): ValidateShipmentResponse
    {
        $this->addAuthHeader();
        return parent::validateShipment($requestType);
    }

    public function createShipmentOrder(CreateShipmentOrderRequest $requestType): CreateShipmentOrderResponse
    {
        $this->addAuthHeader();
        return parent::createShipmentOrder($requestType);
    }

    public function deleteShipmentOrder(DeleteShipmentOrderRequest $requestType): DeleteShipmentOrderResponse
    {
        $this->addAuthHeader();
        return parent::deleteShipmentOrder($requestType);
    }
}
