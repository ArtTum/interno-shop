<?php

namespace App\Services\ERP\Users\User\Interfaces;

use Illuminate\Http\JsonResponse;

interface UserServiceInterface
{
    public function fetch(array $data): JsonResponse;

    public function fetchByField(array $data): JsonResponse;

    public function insert(array $data): JsonResponse;

    public function update(array $data): JsonResponse;

    public function delete(int $id): JsonResponse;

    public function fetchParams(): JsonResponse;
}
