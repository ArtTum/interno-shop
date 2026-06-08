<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\ParcelDe;

class ShipmentResponse
{
    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseType\Status|null
     */
    private $status;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseType\Item[]
     */
    private $items;

    /**
     * @return \App\Libraries\dhl\Model\ParcelDe\ResponseType\Status|null
     */
    public function getStatus(): ?\App\Libraries\dhl\Model\ParcelDe\ResponseType\Status
    {
        return $this->status;
    }

    /**
     * @return \App\Libraries\dhl\Model\ParcelDe\ResponseType\Item[]
     */
    public function getItems(): array
    {
        if (empty($this->items)) {
            return [];
        }

        return $this->items;
    }
}
