<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\MySharedCart\FetchRequest;
use App\Services\ERP\Marketing\SharedCart\SharedCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SharedCartController extends Controller
{
    private SharedCartService $service;

    public function __construct(SharedCartService $service)
    {
        $this->service = $service;
    }

    public function fetch(FetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('shared-carts-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
