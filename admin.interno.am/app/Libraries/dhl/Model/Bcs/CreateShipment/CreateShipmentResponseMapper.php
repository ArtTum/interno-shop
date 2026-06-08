<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\CreateShipment;

use App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\CreationState;
use App\Libraries\dhl\Service\ShipmentService\Shipment;

class CreateShipmentResponseMapper
{
    /**
     * Map the webservice data structure to response objects suitable for third-party consumption.
     *
     * Note: With the SoapClient SOAP_SINGLE_ELEMENT_ARRAYS feature enabled, $creationStates is always an array.
     *
     * @param CreateShipmentOrderResponse $shipmentResponseType
     * @return \App\Libraries\dhl\Api\Data\ShipmentInterface[]
     */
    public function map(CreateShipmentOrderResponse $shipmentResponseType): array
    {
        /** @var \App\Libraries\dhl\Model\Bcs\CreateShipment\ResponseType\CreationState[] $creationStates */
        $creationStates = $shipmentResponseType->getCreationState();

        $shipments = array_map(function (CreationState $creationState) {
            if ($creationState->getLabelData()->getStatus()->getStatusCode() !== 0) {
                // validation error occurred that did not lead to an exception. no label was created.
                return null;
            }

            $shipment = new Shipment(
                $creationState->getSequenceNumber(),
                (string) $creationState->getShipmentNumber(),
                (string) $creationState->getReturnShipmentNumber(),
                (string) $creationState->getLabelData()->getLabelData(),
                (string) $creationState->getLabelData()->getReturnLabelData(),
                (string) $creationState->getLabelData()->getExportLabelData(),
                (string) $creationState->getLabelData()->getCodLabelData()
            );

            return $shipment;
        }, $creationStates);

        return array_filter($shipments);
    }
}
