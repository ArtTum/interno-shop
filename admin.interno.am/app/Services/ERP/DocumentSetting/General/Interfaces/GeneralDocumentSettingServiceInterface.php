<?php

namespace App\Services\ERP\DocumentSetting\General\Interfaces;

use Illuminate\Http\JsonResponse;

interface GeneralDocumentSettingServiceInterface
{
    public function fetchByField(array $data): JsonResponse;

    public function update(array $data): JsonResponse;

    public function fetchParams(array $data): JsonResponse;
}
