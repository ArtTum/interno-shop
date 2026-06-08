<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\EmailSetting\ApproveTranslationRequest;
use App\Http\Requests\ERP\EmailSetting\EmailSettingFetchByFieldRequest;
use App\Http\Requests\ERP\EmailSetting\EmailSettingFetchRequest;
use App\Http\Requests\ERP\EmailSetting\EmailSettingUpdateRequest;
use App\Http\Requests\ERP\EmailSetting\TranslateAIRequest;
use App\Services\ERP\Settings\EmailSetting\EmailSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailSettingController extends Controller
{
    private EmailSettingService $service;

    public function __construct(EmailSettingService $service)
    {
        $this->service = $service;
    }

    public function fetch(EmailSettingFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('email-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(EmailSettingFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('email-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(EmailSettingUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('email-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function translateAI(TranslateAIRequest $request): JsonResponse
    {
        try {
            return $this->service->translateAI($request->validated());
        } catch (\Exception $exception) {
            Log::channel('email-settings-errors')->error('translateAI', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function approveTranslation(ApproveTranslationRequest $request): JsonResponse
    {
        try {
            return $this->service->approveTranslation($request->validated());
        } catch (\Exception $exception) {
            Log::channel('attribute-type-errors')->error('approveTranslation', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function deleteTranslation($id): JsonResponse
    {
        try {
            return $this->service->deleteTranslation((int)$id);
        } catch (\Exception $exception) {
            Log::channel('email-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchParams(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchParams($request->all());
        } catch (\Exception $exception) {
            Log::channel('email-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
