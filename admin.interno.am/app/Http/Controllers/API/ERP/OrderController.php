<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Order\ApplyCouponRequest;
use App\Http\Requests\ERP\Order\CreateAdditionalLabelRequest;
use App\Http\Requests\ERP\Order\CreateFullReshipmentRequest;
use App\Http\Requests\ERP\Order\CreatePartialReshipmentRequest;
use App\Http\Requests\ERP\Order\CreateShippingLabelRequest;
use App\Http\Requests\ERP\Order\CustomerEmailRequest;
use App\Http\Requests\ERP\Order\GetPartialReshipmentInfoRequest;
use App\Http\Requests\ERP\Order\OrderAddFeedbackRequest;
use App\Http\Requests\ERP\Order\OrderGenerateRequest;
use App\Http\Requests\ERP\Order\OrderAddItemRequest;
use App\Http\Requests\ERP\Order\OrderAddNoteRequest;
use App\Http\Requests\ERP\Order\OrderFetchRequest;
use App\Http\Requests\ERP\Order\OrderShowRequest;
use App\Http\Requests\ERP\Order\OrderUpdateRequest;
use App\Services\ERP\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function fetch(OrderFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchIndexParams(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchIndexParams();
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function show(OrderShowRequest $request): JsonResponse
    {
        try {
            return $this->service->show($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchParams(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchParams();
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }


    public function getItemPrice(Request $request): JsonResponse
    {
        try {
            return $this->service->getItemPrice($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(OrderUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function updateJustStatus(Request $request): JsonResponse
    {
        try {
            return $this->service->updateJustStatus($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function addItem(OrderAddItemRequest $request): JsonResponse
    {
        try {
            return $this->service->addItem($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        try {
            return $this->service->applyCoupon($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function addFeedback(OrderAddFeedbackRequest $request): JsonResponse
    {
        try {
            return $this->service->addFeedback($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function createFullReshipment(CreateFullReshipmentRequest $request): JsonResponse
    {
        try {
            return $this->service->createFullReshipment($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function createPartialReshipment(CreatePartialReshipmentRequest $request): JsonResponse
    {
        try {
            return $this->service->createPartialReshipment($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function getPartialReshipmentInfo(GetPartialReshipmentInfoRequest $request): JsonResponse
    {
        try {
            return $this->service->getPartialReshipmentInfo($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function addNote(OrderAddNoteRequest $request): JsonResponse
    {
        try {
            return $this->service->addNote($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function refund(Request $request): JsonResponse
    {
        try {
            return $this->service->refund($request->all(), $request->vendor_key);
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function bulkActions(Request $request): JsonResponse
    {
        try {
            return $this->service->bulkActions($request->selectedItems, $request->status);
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function resendEmail(Request $request): JsonResponse
    {
        try {
            return $this->service->resendEmail($request->id, $request->status);
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function customerEmail(CustomerEmailRequest $request): JsonResponse
    {
        try {
            return $this->service->customerEmail($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

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
            return $this->service->delete((int)$id);
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function deleteDocument($id): JsonResponse
    {
        try {
            return $this->service->deleteDocument((int)$id);
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function generate(OrderGenerateRequest $request): JsonResponse
    {
        try {
            return $this->service->generate($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function showPdf(OrderShowRequest $request): JsonResponse
    {
        try {
            return $this->service->showPdf($request->id, $request->sub_id);
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function showImoDocument(OrderShowRequest $request): JsonResponse
    {
        try {
            return $this->service->showImoDocument($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function createShippingLabel(CreateShippingLabelRequest $request): JsonResponse
    {
        try {
            return $this->service->createShippingLabel($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function createAdditionalLabel(CreateAdditionalLabelRequest $request): JsonResponse
    {
        try {
            return $this->service->createAdditionalLabel($request->validated());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function sendInvoicesByEmails(): JsonResponse
    {
        try {
            return $this->service->sendInvoicesByEmails();
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));

        }
    }

    public function createADBDocument(Request $request): JsonResponse
    {
        try {
            return $this->service->createADBDocument($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));

        }
    }

    public function sendADBDocument(Request $request): JsonResponse
    {
        try {
            return $this->service->sendADBDocument($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));

        }
    }

    public function approvedADBDocument(Request $request): JsonResponse
    {
        try {
            return $this->service->approvedADBDocument($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));

        }
    }

    public function deleteShippingLabel($id): JsonResponse
    {
        try {
            return $this->service->deleteShippingLabel((int)$id);
        } catch (\Exception $exception) {

            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function createEAD(Request $request): JsonResponse
    {
        try {
            return $this->service->createEAD($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));

        }
    }

    public function exportProductsToExcel(Request $request)
    {
        try {
            return $this->service->exportProductsToExcel($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));

        }
    }

    public function exportProductsToExcel2(Request $request)
    {
        try {
            return $this->service->exportProductsToExcel2($request->all());
        } catch (\Exception $exception) {
            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);
            $message = preg_replace('/\s+/', ' ', trim($exception->getMessage()));
            preg_match('/Exception: (.*?) in/', $message, $matches);

            return response()->json([
                'success' => false,
                'message' => $matches[0] ?? $exception->getMessage(),
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));

        }
    }

    public function deleteAdb($id): JsonResponse
    {
        try {
            return $this->service->deleteAdb((int)$id);
        } catch (\Exception $exception) {

            Log::channel('orders-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
