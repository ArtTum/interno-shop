<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\AbandonedEmail\AbandonedEmailFetchRequest;
use App\Services\ERP\AbandonedEmail\AbandonedEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AbandonedEmailController extends Controller
{
    private AbandonedEmailService $service;

    public function __construct(AbandonedEmailService $service)
    {
        $this->service = $service;
    }

    public function fetch(AbandonedEmailFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('abandoned-email-errors')->error('Failed fetch abandoned emails', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
