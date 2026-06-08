<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Provider\ProviderFetchRequest;
use App\Services\ERP\Provider\ProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProviderController extends Controller
{
    private ProviderService $service;

    public function __construct(ProviderService $service)
    {
        $this->service = $service;
    }

    public function fetch(ProviderFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('provider-errors')->error('Failed fetch providers', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
