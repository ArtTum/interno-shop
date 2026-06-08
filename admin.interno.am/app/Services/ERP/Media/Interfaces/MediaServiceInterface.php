<?php

namespace App\Services\ERP\Media\Interfaces;

use Illuminate\Http\JsonResponse;

interface MediaServiceInterface
{
    public function fetch(array $data): JsonResponse;

    public function fetchByField(array $data): JsonResponse;

    public function insert(array $data): JsonResponse;

    public function update(array $data): JsonResponse;

    public function delete(array $id): JsonResponse;
}
