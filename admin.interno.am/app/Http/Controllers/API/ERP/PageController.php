<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Page\ApproveTranslationRequest;
use App\Http\Requests\ERP\Page\PageCloneRequest;
use App\Http\Requests\ERP\Page\PageFetchByFieldRequest;
use App\Http\Requests\ERP\Page\PageFetchRequest;
use App\Http\Requests\ERP\Page\PageInsertRequest;
use App\Http\Requests\ERP\Page\PageUpdateRequest;
use App\Http\Requests\ERP\Page\TranslateAIRequest;
use App\Services\ERP\Content\Page\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PageController extends Controller
{
    private PageService $service;

    public function __construct(PageService $service)
    {
        $this->service = $service;
    }

    public function fetch(PageFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function recover(Request $request): JsonResponse
    {
        try {
            return $this->service->recover($request->all());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(PageFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(PageInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(PageUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated(), $request);
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
    public function translateAI(TranslateAIRequest $request): JsonResponse
    {
        try {
            return $this->service->translateAI($request->validated());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('translateAI', ['error' => $exception]);

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
            Log::channel('category-errors')->error('approveTranslation', ['error' => $exception]);

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
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

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
            return $this->service->deleteTranslation((int) $id);
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

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
            return $this->service->fetchParams($request->all());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function generatePath(Request $request): JsonResponse
    {
        try {
            return $this->service->generatePath($request->all());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function clone(PageCloneRequest $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        try {
            return $this->service->clone($request->validated());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function upload(Request $request): JsonResponse
    {
        try {
            return $this->service->upload($request->all(), $request);
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->export($request->all());
        } catch (\Exception $exception) {
            Log::channel('pages-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
