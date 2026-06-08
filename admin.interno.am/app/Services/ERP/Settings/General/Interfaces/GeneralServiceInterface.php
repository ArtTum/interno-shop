<?php

namespace App\Services\ERP\Settings\General\Interfaces;

use Illuminate\Http\JsonResponse;

interface GeneralServiceInterface
{
    public function fetch(array $data): JsonResponse;

    public function fetchByField(array $data): JsonResponse;

    public function update(array $data): JsonResponse;
}
