<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\ValidateShipment;

use App\Libraries\dhl\Model\Bcs\Common\AbstractRequest;
use App\Libraries\dhl\Model\Bcs\Common\Version;
use App\Libraries\dhl\Model\Bcs\ValidateShipment\RequestType\ShipmentOrderType;

class ValidateShipmentOrderRequest extends AbstractRequest
{
    /**
     * ShipmentOrder is the highest parent element that contains all data with respect to one shipment order.
     *
     * @var \stdClass[]|ShipmentOrderType[] $ShipmentOrder
     */
    protected $ShipmentOrder;

    /**
     * @param \App\Libraries\dhl\Model\Bcs\Common\Version $version
     * @param \stdClass[]|ShipmentOrderType[] $shipmentOrders
     */
    public function __construct(Version $version, array $shipmentOrders)
    {
        $this->ShipmentOrder = $shipmentOrders;

        parent::__construct($version);
    }
}
