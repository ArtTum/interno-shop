<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\DeleteShipment;

use App\Libraries\dhl\Model\Bcs\Common\AbstractRequest;
use App\Libraries\dhl\Model\Bcs\Common\Version;

class DeleteShipmentOrderRequest extends AbstractRequest
{
    /**
     * Can contain any DHL shipment number.
     *
     * @var string[] $shipmentNumber
     */
    protected $shipmentNumber;

    /**
     * @param \App\Libraries\dhl\Model\Bcs\Common\Version $Version
     * @param string[] $shipmentNumbers
     */
    public function __construct(Version $Version, array $shipmentNumbers)
    {
        $this->shipmentNumber = $shipmentNumbers;

        parent::__construct($Version);
    }
}
