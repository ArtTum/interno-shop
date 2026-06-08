<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\MyOffer\FetchRequest;
use App\Services\ERP\Marketing\MyOffer\MyOfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MyOfferController extends Controller
{
    private MyOfferService $service;

    public function __construct(MyOfferService $service)
    {
        $this->service = $service;
    }

    public function fetch(FetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('my-offer-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
