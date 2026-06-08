<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\SmsHistory\SmsHistoryFetchByFieldRequest;
use App\Http\Requests\ERP\SmsHistory\SmsHistoryFetchRequest;
use App\Http\Requests\ERP\SmsHistory\SmsHistoryInsertRequest;
use App\Http\Requests\ERP\SmsHistory\SmsHistoryUpdateRequest;
use App\Services\ERP\SmsHistory\SmsHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsHistoryController extends Controller
{
    private SmsHistoryService $service;

    public function __construct(SmsHistoryService $service)
    {
        $this->service = $service;
    }

    public function fetchIndexParams(): JsonResponse
    {
        try {
            return $this->service->fetchIndexParams();
        } catch (\Exception $exception) {
            Log::channel('sms-historiess-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetch(SmsHistoryFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('sms-historiess-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(SmsHistoryFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('sms-historiess-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(SmsHistoryInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('sms-historiess-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(SmsHistoryUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('sms-historiess-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            return $this->service->delete((int) $id);
        } catch (\Exception $exception) {
            Log::channel('sms-historiess-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'    => 'required|string',
            'sms_text' => 'required|string',
        ]);

        try {
            return $this->service->send($data);
        } catch (\Exception $exception) {
            Log::channel('sms-historiess-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
