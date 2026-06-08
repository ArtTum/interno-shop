<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\ValidateShipment\ResponseType;

use App\Libraries\dhl\Model\Bcs\Common\StatusInformation;

class ValidationState
{
    /**
     * Identifier for ShipmentOrder set by client application in ValidateShipment request.
     *
     * @var string $sequenceNumber
     */
    protected $sequenceNumber;

    /**
     * @var \App\Libraries\dhl\Model\Bcs\Common\StatusInformation $Status
     */
    protected $Status;

    /**
     * @return string
     */
    public function getSequenceNumber(): string
    {
        return $this->sequenceNumber;
    }

    /**
     * @return \App\Libraries\dhl\Model\Bcs\Common\StatusInformation
     */
    public function getStatus(): StatusInformation
    {
        return $this->Status;
    }
}
