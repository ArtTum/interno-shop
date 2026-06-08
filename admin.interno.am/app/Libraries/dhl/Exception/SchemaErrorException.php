<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Exception;

use Http\Client\Exception\RequestException;

class SchemaErrorException extends RequestException
{
}
