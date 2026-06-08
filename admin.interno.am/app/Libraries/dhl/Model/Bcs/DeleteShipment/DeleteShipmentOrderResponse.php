<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\DeleteShipment;

use App\Libraries\dhl\Model\Bcs\Common\AbstractResponse;
use App\Libraries\dhl\Model\Bcs\DeleteShipment\ResponseType\DeletionState;

class DeleteShipmentOrderResponse extends AbstractResponse
{
    /**
     * For every ShipmentNumber requested, one DeletionState node is returned
     * that contains the status of the respective deletion operation.
     *
     * @var DeletionState|\App\Libraries\dhl\Model\Bcs\DeleteShipment\ResponseType\DeletionState[]|null $DeletionState
     */
    protected $DeletionState = null;

    /**
     * @return \App\Libraries\dhl\Model\Bcs\DeleteShipment\ResponseType\DeletionState[]
     */
    public function getDeletionState(): array
    {
        if (empty($this->DeletionState)) {
            return [];
        }

        if (!\is_array($this->DeletionState)) {
            return [$this->DeletionState];
        }

        return $this->DeletionState;
    }
}
