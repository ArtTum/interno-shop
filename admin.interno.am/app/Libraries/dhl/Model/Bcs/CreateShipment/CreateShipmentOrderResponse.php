<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\CreateShipment;

use App\Libraries\dhl\Model\Bcs\Common\AbstractResponse;
use App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\CreationState;

class CreateShipmentOrderResponse extends AbstractResponse
{
    /**
     * The operation's success status for every single ShipmentOrder will be returned by one CreationState element.
     * It is identifiable via SequenceNumber.
     *
     * @var \App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\CreationState[]|CreationState|null $CreationState
     */
    protected $CreationState = null;

    /**
     * @return CreationState[]
     */
    public function getCreationState(): array
    {
        if (empty($this->CreationState)) {
            return [];
        }

        if (!\is_array($this->CreationState)) {
            return [$this->CreationState];
        }

        return $this->CreationState;
    }
}
