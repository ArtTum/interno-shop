<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Soap;

use App\Libraries\dhl\Model\Bcs\Common\Version;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\GetVersion\GetVersionResponse;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponse;

class Client extends AbstractClient
{
    /**
     * @var \SoapClient
     */
    private $soapClient;

    public function __construct(\SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
    }

    /**
     * GetVersion is the operation call used to query the latest version available on the web.
     *
     * @param \App\Libraries\dhl\Model\Bcs\Common\Version $requestType
     *
     * @return GetVersionResponse
     * @throws \SoapFault
     */
    public function getVersion(Version $requestType): GetVersionResponse
    {
        return $this->soapClient->__soapCall(__FUNCTION__, [$requestType]);
    }

    /**
     * ValidateShipmentOrder is the operation call used to validate shipments before booking label and tracking number.
     *
     * @param \App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentOrderRequest $requestType
     *
     * @return \App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponse
     * @throws \SoapFault
     */
    public function validateShipment(ValidateShipmentOrderRequest $requestType): ValidateShipmentResponse
    {
        return $this->soapClient->__soapCall(__FUNCTION__, [$requestType]);
    }

    /**
     * CreateShipmentOrder is the operation call used to generate shipments with the relevant DHL Paket labels.
     *
     * @param CreateShipmentOrderRequest $requestType
     *
     * @return \App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse
     * @throws \SoapFault
     */
    public function createShipmentOrder(CreateShipmentOrderRequest $requestType): CreateShipmentOrderResponse
    {
        return $this->soapClient->__soapCall(__FUNCTION__, [$requestType]);
    }

    /**
     * @param \App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderRequest $requestType
     *
     * @return \App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse
     * @throws \SoapFault
     */
    public function deleteShipmentOrder(DeleteShipmentOrderRequest $requestType): DeleteShipmentOrderResponse
    {
        return $this->soapClient->__soapCall(__FUNCTION__, [$requestType]);
    }
}
