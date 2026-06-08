<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\ValidateShipment\RequestType;

class ShipperType extends ShipperTypeType
{
    public function __construct(NameType $name, NativeAddressType $address)
    {
        parent::__construct($name, $address);
    }
}
