<?php

namespace App\Services\ERP\File\Interfaces;

use Illuminate\Http\JsonResponse;

interface FileServiceInterface
{
    public function create(array $data, string $folder, string $fileName, string $name): String;
}
