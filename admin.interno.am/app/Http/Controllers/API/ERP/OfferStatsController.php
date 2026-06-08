<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\OfferStats\FetchRequest;
use App\Services\ERP\Marketing\OfferStats\OfferStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OfferStatsController extends Controller
{
    private OfferStatsService $service;

    public function __construct(OfferStatsService $service)
    {
        $this->service = $service;
    }

    public function fetch(FetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('shared-carts-stats-errors')->error('Failed fetch cart stats', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
