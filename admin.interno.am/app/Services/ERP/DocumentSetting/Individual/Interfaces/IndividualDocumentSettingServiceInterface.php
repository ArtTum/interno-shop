<?php

namespace App\Services\ERP\DocumentSetting\Individual\Interfaces;

use Illuminate\Http\JsonResponse;

interface IndividualDocumentSettingServiceInterface
{
    public function fetch(array $data): JsonResponse;

    public function fetchByField(array $data): JsonResponse;

    public function update(array $data): JsonResponse;
}
