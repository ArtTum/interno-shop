<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Services\ERP\Tools\DatabaseRestore\DatabaseRestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DatabaseRestoreController extends Controller
{
    private DatabaseRestoreService $service;

    public function __construct(DatabaseRestoreService $service)
    {
        $this->service = $service;
    }

    public function listBackups(): JsonResponse
    {
        return $this->service->list();
    }

    public function import(Request $request): JsonResponse
    {
        try {
            return $this->service->import($request->get('filename'));
        } catch (\Exception $e) {
            Log::error("Import failed", ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
