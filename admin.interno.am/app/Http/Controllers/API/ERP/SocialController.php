<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Social\SocialFetchRequest;
use App\Http\Requests\ERP\Social\SocialUpdateRequest;
use App\Services\ERP\Settings\Social\SocialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SocialController extends Controller
{
    private SocialService $service;

    public function __construct(SocialService $service)
    {
        $this->service = $service;
    }

    public function fetch(SocialFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('socials-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(SocialUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('socials-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
