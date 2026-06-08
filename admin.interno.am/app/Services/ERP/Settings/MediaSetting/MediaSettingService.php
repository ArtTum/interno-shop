<?php

namespace App\Services\ERP\Settings\MediaSetting;

use App\Events\ReloadPagePublic;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Services\ERP\Settings\MediaSetting\Interfaces\MediaSettingServiceInterface;
use Illuminate\Http\JsonResponse;

class MediaSettingService implements MediaSettingServiceInterface
{
    private MediaSettingRepository $repository;

    public function __construct(MediaSettingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, name, width, height";

        $ordering = [
            'field' => 'id',
            'direction' => 'desc'
        ];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, [], $ordering, $data, [], []),
        ]);
    }

    public function update(array $data): JsonResponse
    {
        foreach ($data['row'] as $item) {
            $this->repository->update('id', $item['id'], $item);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

}
