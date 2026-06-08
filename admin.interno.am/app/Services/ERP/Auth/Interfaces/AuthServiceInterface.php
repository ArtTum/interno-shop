<?php

namespace App\Services\ERP\Auth\Interfaces;

use Illuminate\Http\JsonResponse;

interface AuthServiceInterface
{
    public function create(array $data): JsonResponse;
    public function login(array $data): JsonResponse;
    public function fetch(): JsonResponse;
}
