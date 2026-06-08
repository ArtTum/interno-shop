<?php

namespace App\Services\ERP\Settings\MediaSetting\Interfaces;

use Illuminate\Http\JsonResponse;

interface MediaSettingServiceInterface
{
    public function fetch(array $data): JsonResponse;

    public function update(array $data): JsonResponse;

}
