<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\ParcelDe\ResponseMapper;

use App\Libraries\dhl\Model\ParcelDe\ResponseType\ValidationMessage;
use App\Libraries\dhl\Model\ParcelDe\ShipmentResponse;
use App\Libraries\dhl\Service\ShipmentService\ValidationResult;

class ValidateShipmentResponseMapper
{
    /**
     * Map the webservice data structure to response objects suitable for third-party consumption.
     *
     * @param \App\Libraries\dhl\Model\ParcelDe\ShipmentResponse $response
     * @return \App\Libraries\dhl\Api\Data\ValidationResultInterface[]
     */
    public function map(ShipmentResponse $response): array
    {
        $results = [];

        foreach ($response->getItems() as $index => $item) {
            if (!empty($item->getValidationMessages())) {
                $itemMessages = array_map(
                    function (ValidationMessage $itemMessage) {
                        return sprintf(
                            '%s (%s): %s',
                            $itemMessage->getValidationState(),
                            $itemMessage->getProperty(),
                            $itemMessage->getValidationMessage()
                        );
                    },
                    $item->getValidationMessages()
                );

                $message = implode("\n", $itemMessages);
            } else {
                $message = $item->getStatus()->getDetail() ?? $item->getStatus()->getTitle();
            }

            $results[] = new ValidationResult((string) $index, ($item->getStatus()->getStatusCode() === 200), $message);
        }

        return $results;
    }
}
