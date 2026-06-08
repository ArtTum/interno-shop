<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Api;

use App\Libraries\dhl\Api\Data\OrderConfigurationInterface;
use App\Libraries\dhl\Api\Data\ShipmentInterface;
use App\Libraries\dhl\Api\Data\ValidationResultInterface;
use App\Libraries\dhl\Exception\DetailedServiceException;
use App\Libraries\dhl\Exception\ServiceException;

/**
 * @api
 */
interface ShipmentServiceInterface
{
    /**
     * GetVersion is the operation call used to query the latest version available on the web.
     *
     * @return string
     *
     * @throws \App\Libraries\dhl\Exception\AuthenticationException
     * @throws \App\Libraries\dhl\Exception\DetailedServiceException
     * @throws ServiceException
     */
    public function getVersion(): string;

    /**
     * ValidateShipmentOrder is the operation call used to validate shipments before booking label and tracking number.
     *
     * @param \stdClass[] $shipmentOrders
     * @param OrderConfigurationInterface|null $configuration
     *
     * @return ValidationResultInterface[]
     *
     * @throws \App\Libraries\dhl\Exception\AuthenticationException
     * @throws \App\Libraries\dhl\Exception\DetailedServiceException
     * @throws \App\Libraries\dhl\Exception\ServiceException
     */
    public function validateShipments(array $shipmentOrders, OrderConfigurationInterface $configuration = null): array;

    /**
     * CreateShipmentOrder is the operation call used to generate shipments with the relevant DHL Paket labels.
     *
     * @param \stdClass[] $shipmentOrders
     * @param OrderConfigurationInterface|null $configuration
     *
     * @return ShipmentInterface[]
     *
     * @throws \App\Libraries\dhl\Exception\AuthenticationException
     * @throws \App\Libraries\dhl\Exception\DetailedServiceException
     * @throws ServiceException
     */
    public function createShipments(array $shipmentOrders, OrderConfigurationInterface $configuration = null): array;

    /**
     * DeleteShipmentOrder is the operation call used to cancel created shipments.
     *
     * Note that cancellation is only possible before the end-of-the-day manifest.
     *
     * @param string[] $shipmentNumbers
     * @param string $profile
     *
     * @return string[]
     *
     * @throws \App\Libraries\dhl\Exception\AuthenticationException
     * @throws DetailedServiceException
     * @throws ServiceException
     */
    public function cancelShipments(
        array $shipmentNumbers,
        string $profile = OrderConfigurationInterface::DEFAULT_PROFILE
    ): array;
}
