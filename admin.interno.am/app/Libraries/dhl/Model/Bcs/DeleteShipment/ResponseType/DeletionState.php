<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\DeleteShipment\ResponseType;

use App\Libraries\dhl\Model\Bcs\Common\StatusInformation;

class DeletionState
{
    /**
     * Can contain any DHL shipment number.
     *
     * @var string $shipmentNumber
     */
    protected $shipmentNumber;

    /**
     * Success status of processing the deletion of particular shipment.
     *
     * @var StatusInformation $Status
     */
    protected $Status;

    /**
     * @return string
     */
    public function getShipmentNumber(): string
    {
        return $this->shipmentNumber;
    }

    /**
     * @return StatusInformation
     */
    public function getStatus(): StatusInformation
    {
        return $this->Status;
    }
}
