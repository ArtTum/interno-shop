<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\AccountingSetting\AccountingSettingFetchRequest;
use App\Http\Requests\ERP\AccountingSetting\AccountingSettingUpdateRequest;
use App\Services\ERP\Accounting\Setting\AccountingSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AccountingSettingController extends Controller
{
    private AccountingSettingService $service;

    public function __construct(AccountingSettingService $service)
    {
        $this->service = $service;
    }

    public function fetch(AccountingSettingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('accounting-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(AccountingSettingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('accounting-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
