<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\ParcelDe\ResponseMapper;

use App\Libraries\dhl\Model\ParcelDe\ResponseType\Label;
use App\Libraries\dhl\Model\ParcelDe\ShipmentResponse;
use App\Libraries\dhl\Service\ShipmentService\Shipment;

class CreateShipmentResponseMapper
{
    /**
     * Map the webservice data structure to response objects suitable for third-party consumption.
     *
     * @param ShipmentResponse $response
     * @return \App\Libraries\dhl\Api\Data\ShipmentInterface[]
     */
    public function map(ShipmentResponse $response): array
    {
        $results = [];

        foreach ($response->getItems() as $index => $item) {
            if ($item->getStatus()->getStatusCode() !== 200) {
                // validation error occurred that did not lead to an exception. no label was created.
                continue;
            }

            $results[] = new Shipment(
                (string) $index,
                $item->getShipmentNo() ?? '',
                $item->getReturnShipmentNo() ?? '',
                $item->getLabel() instanceof Label ? (string) $item->getLabel()->getB64() : '',
                $item->getReturnLabel() instanceof Label ? (string) $item->getReturnLabel()->getB64() : '',
                $item->getCustomsDoc() instanceof Label ? (string) $item->getCustomsDoc()->getB64() : '',
                $item->getCodLabel() instanceof Label ? (string) $item->getCodLabel()->getB64() : ''
            );
        }

        return $results;
    }
}
