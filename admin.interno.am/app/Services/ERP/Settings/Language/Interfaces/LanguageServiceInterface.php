<?php

namespace App\Services\ERP\Settings\Language\Interfaces;

use Illuminate\Http\JsonResponse;

interface LanguageServiceInterface
{
    public function fetch(array $data): JsonResponse;

    public function fetchByField(array $data): JsonResponse;

    public function insert(array $data): JsonResponse;

    public function update(array $data): JsonResponse;

    public function delete(int $id): JsonResponse;
}
