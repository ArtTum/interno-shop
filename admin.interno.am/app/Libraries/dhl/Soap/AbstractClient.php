<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Soap;

use App\Libraries\dhl\Exception\AuthenticationErrorException;
use App\Libraries\dhl\Exception\DetailedErrorException;
use App\Libraries\dhl\Model\Bcs\Common\Version;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse;
use App\Libraries\dhl\Model\Bcs\GetVersion\GetVersionResponse;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentOrderRequest;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ValidateShipmentResponse;

abstract class AbstractClient
{
    /**
     * GetVersion is the operation call used to query the latest version available on the web.
     *
     * @param \App\Libraries\dhl\Model\Bcs\Common\Version $requestType
     *
     * @return \App\Libraries\dhl\Model\Bcs\GetVersion\GetVersionResponse
     *
     * @throws \App\Libraries\dhl\Exception\AuthenticationErrorException
     * @throws \App\Libraries\dhl\Exception\DetailedErrorException
     * @throws \SoapFault
     */
    abstract public function getVersion(Version $requestType): GetVersionResponse;

    /**
     * ValidateShipmentOrder is the operation call used to validate shipments before booking label and tracking number.
     *
     * @param ValidateShipmentOrderRequest $requestType
     *
     * @return ValidateShipmentResponse
     *
     * @throws \App\Libraries\dhl\Exception\AuthenticationErrorException
     * @throws DetailedErrorException
     * @throws \SoapFault
     */
    abstract public function validateShipment(ValidateShipmentOrderRequest $requestType): ValidateShipmentResponse;

    /**
     * CreateShipmentOrder is the operation call used to generate shipments with the relevant DHL Paket labels.
     *
     * @param \App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderRequest $requestType
     *
     * @return \App\Libraries\dhl\Model\Bcs\CreateShipment\CreateShipmentOrderResponse
     *
     * @throws \App\Libraries\dhl\Exception\AuthenticationErrorException
     * @throws \App\Libraries\dhl\Exception\DetailedErrorException
     * @throws \SoapFault
     */
    abstract public function createShipmentOrder(CreateShipmentOrderRequest $requestType): CreateShipmentOrderResponse;

    /**
     * DeleteShipmentOrder is the operation call used to cancel created shipments.
     *
     * Note that cancellation is only possible before the end-of-the-day manifest.
     *
     * @param \App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderRequest $requestType
     *
     * @return \App\Libraries\dhl\Model\Bcs\DeleteShipment\DeleteShipmentOrderResponse
     *
     * @throws AuthenticationErrorException
     * @throws \App\Libraries\dhl\Exception\DetailedErrorException
     * @throws \SoapFault
     */
    abstract public function deleteShipmentOrder(DeleteShipmentOrderRequest $requestType): DeleteShipmentOrderResponse;
}
