<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\Bcs\ValidateShipment\RequestType;

class ReceiverType extends ReceiverTypeType
{
    /**
     * @param string $name1
     * @param ReceiverNativeAddressType|null $address Conditionally mandatory.
     *        If omitted, set PackStation or Postfiliale instead.
     */
    public function __construct(
        string $name1,
        ReceiverNativeAddressType $address = null
    ) {
        parent::__construct($name1, $address);
    }
}
