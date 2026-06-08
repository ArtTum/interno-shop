<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Media\MediaFetchByFieldRequest;
use App\Http\Requests\ERP\Media\MediaFetchRequest;
use App\Http\Requests\ERP\Media\MediaUpdateRequest;
use App\Http\Requests\ERP\Media\MediaUploadRequest;
use App\Http\Requests\ERP\Media\TranslateAIRequest;
use App\Services\ERP\Media\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    private MediaService $service;

    public function __construct(MediaService $service)
    {
        $this->service = $service;
    }

    public function fetch(MediaFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(MediaFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function upload(MediaUploadRequest $request)
    {
        try {
            return $this->service->insert($request->all());
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function replaceImage(Request $request)
    {
        try {
            return $this->service->replaceImage($request->all());
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(MediaUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('categories-errors')->error('translateAI', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function uploadFile(Request $request): JsonResponse
    {
        try {
            return $this->service->uploadFile($request->all(), $request);
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function delete($ids): JsonResponse
    {
        try {
            $ids = explode(',', $ids);
            return $this->service->delete($ids);
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function squareImage(Request $request)
    {
        $mainImage = $request->image;

        try {
            return $this->service->showSquareImageFromUrl($mainImage);
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Detect needed attribute values failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function overlay(Request $request)
    {
        try {
            return $this->service->overlay();
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Detect needed attribute values failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
    public function overlayWatermark(Request $request)
    {
        try {
            $params = $request->get('params');
            return $this->service->overlayWatermark($params);
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Detect needed attribute values failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function watermarkImage(string $vendor_key, int $product_id, int $attribute_id, int $language_id)
    {
        try {
            return $this->service->watermarkImage($vendor_key, $product_id, $attribute_id, $language_id);
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Detect needed attribute values failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->service->export($request->all());
        } catch (\Exception $exception) {
            Log::channel('medias-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
