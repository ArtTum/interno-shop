<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Exception;

/**
 * Class DetailedServiceException
 *
 * A special instance of the ServiceException which is able to
 * provide a meaningful error message, suitable for UI display.
 *
 * @api
 */
class DetailedServiceException extends ServiceException
{
}
