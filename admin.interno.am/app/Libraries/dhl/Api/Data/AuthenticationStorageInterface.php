<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace App\Libraries\dhl\Api\Data;

/**
 * @api
 */
interface AuthenticationStorageInterface
{
    /**
     * @return string
     */
    public function getApplicationId(): string;

    /**
     * @return string
     */
    public function getApplicationToken(): string;

    /**
     * @return string
     */
    public function getUser(): string;

    /**
     * @return string
     */
    public function getSignature(): string;
}
