<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Lead\LeadFetchRequest;
use App\Http\Requests\ERP\Lead\LeadRegeneratePdfRequest;
use App\Http\Requests\ERP\Lead\LeadUpdateRequest;
use App\Services\ERP\Leads\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadController extends Controller
{
    private LeadService $service;

    public function __construct(LeadService $service)
    {
        $this->service = $service;
    }

    public function fetch(LeadFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('leads-errors')->error('Failed fetch leads', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function download(Request $request): BinaryFileResponse
    {
        return $this->service->download($request->all());
    }

    public function update(LeadUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('leads-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function regeneratePDF(LeadRegeneratePdfRequest $request): JsonResponse
    {
        try {
            return $this->service->regeneratePDF($request->validated());
        } catch (\Exception $exception) {
            Log::channel('leads-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function exportLeads(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->exportLeads($request->all());
        } catch (\Exception $exception) {
            Log::channel('leads-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
