<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Model\ParcelDe\RequestType;

class ShipperAddress extends Address implements ShipperInterface, \JsonSerializable
{
}
