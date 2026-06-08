<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Services\ERP\Newsletter\Report\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NewsletterReportController extends Controller
{
    private ReportService $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function fetchBoxes(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchBoxes($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchOrdersChart(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchOrdersChart($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchCustomersChart(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchCustomersChart($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchBillingCountriesChart(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchBillingCountriesChart($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchShippingCountriesChart(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchShippingCountriesChart($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchLanguagesChart(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchLanguagesChart($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchTopProducts(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchTopProducts($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchTopPaymentMethods(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchTopPaymentMethods($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('fetchTopPaymentMethods', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchPaymentMethodsChart(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchPaymentMethodsChart($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('fetchPaymentMethodsChart', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchTopItems(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchTopItems($request->all());
        } catch (\Exception $exception) {
            Log::channel('newsletter-report-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
