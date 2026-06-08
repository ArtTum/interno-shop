<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\ValidateShipment;

use App\Libraries\dhl\Model\Bcs\Common\AbstractResponse;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\ResponseType\ValidationState;

class ValidateShipmentResponse extends AbstractResponse
{
    /**
     * The operation's success status for every single ShipmentOrder will be returned by one ValidationState element.
     * It is identifiable via SequenceNumber.
     *
     * @var \App\Libraries\dhl\Model\Bcs\ValidateShipment\ResponseType\ValidationState[]|ValidationState|null $ValidationState
     */
    protected $ValidationState = null;

    /**
     * @return \App\Libraries\dhl\Model\Bcs\ValidateShipment\ResponseType\ValidationState[]
     */
    public function getValidationState(): array
    {
        if (empty($this->ValidationState)) {
            return [];
        }

        if (!\is_array($this->ValidationState)) {
            return [$this->ValidationState];
        }

        return $this->ValidationState;
    }
}
