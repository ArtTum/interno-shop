<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\PaymentMethod\PaymentMethodAccountUpdateRequest;
use App\Http\Requests\ERP\PaymentMethod\PaymentMethodFetchByFieldRequest;
use App\Http\Requests\ERP\PaymentMethod\PaymentMethodFetchRequest;
use App\Http\Requests\ERP\PaymentMethod\PaymentMethodGenerateAccountsRequest;
use App\Http\Requests\ERP\PaymentMethod\PaymentMethodUpdatePriorityRequest;
use App\Http\Requests\ERP\PaymentMethod\PaymentMethodUpdateRequest;
use App\Services\ERP\Settings\PaymentMethod\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    private PaymentMethodService $service;

    public function __construct(PaymentMethodService $service)
    {
        $this->service = $service;
    }

    public function fetch(PaymentMethodFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(PaymentMethodFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(PaymentMethodUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchAccounts(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchAccounts($request->all());
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function updatePriority(PaymentMethodUpdatePriorityRequest $request): JsonResponse
    {
        try {
            return $this->service->updatePriority($request->validated());
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function generateAccounts(PaymentMethodGenerateAccountsRequest $request): JsonResponse
    {
        try {
            return $this->service->generateAccounts($request->validated());
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function deleteAccounts($methodId, $deleteInvalids): JsonResponse
    {
        try {
            return $this->service->deleteAccounts((int)$methodId, filter_var($deleteInvalids, FILTER_VALIDATE_BOOLEAN));
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function deleteAccount($id): JsonResponse
    {
        try {
            return $this->service->deleteAccount((int)$id);
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function updateAccount(PaymentMethodAccountUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->updateAccount($request->validated(), $request);
        } catch (\Exception $exception) {
            Log::channel('payment-methods-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
