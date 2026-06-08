<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\ParcelDe\ResponseType;

class Item
{
    /**
     * @var Status
     */
    private $sstatus;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseType\ValidationMessage[]
     */
    private $validationMessages;

    /**
     * @var string|null
     */
    private $shipmentNo;

    /**
     * @var string|null
     */
    private $returnShipmentNo;

    /**
     * @var string|null
     */
    private $shipmentRefNo;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseType\Label|null
     */
    private $label;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseType\Label|null
     */
    private $returnLabel;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseType\Label|null
     */
    private $customsDoc;

    /**
     * @var \App\Libraries\dhl\Model\ParcelDe\ResponseType\Label|null
     */
    private $codLabel;

    public function getStatus(): Status
    {
        return $this->sstatus;
    }

    /**
     * @return \App\Libraries\dhl\Model\ParcelDe\ResponseType\ValidationMessage[]
     */
    public function getValidationMessages(): array
    {
        if (empty($this->validationMessages)) {
            return [];
        }

        return $this->validationMessages;
    }

    public function getShipmentNo(): ?string
    {
        return $this->shipmentNo;
    }

    public function getReturnShipmentNo(): ?string
    {
        return $this->returnShipmentNo;
    }

    public function getShipmentRefNo(): ?string
    {
        return $this->shipmentRefNo;
    }

    public function getLabel(): ?Label
    {
        return $this->label;
    }

    public function getReturnLabel(): ?Label
    {
        return $this->returnLabel;
    }

    public function getCustomsDoc(): ?Label
    {
        return $this->customsDoc;
    }

    public function getCodLabel(): ?Label
    {
        return $this->codLabel;
    }
}
