<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Product\ApproveTranslationRequest;
use App\Http\Requests\ERP\Product\ProductAutocompleteRequest;
use App\Http\Requests\ERP\Product\ProductBulkDeleteRequest;
use App\Http\Requests\ERP\Product\ProductCloneRequest;
use App\Http\Requests\ERP\Product\ProductFetchByFieldRequest;
use App\Http\Requests\ERP\Product\ProductFetchRequest;
use App\Http\Requests\ERP\Product\ProductGenerateVariationsRequest;
use App\Http\Requests\ERP\Product\ProductInsertRequest;
use App\Http\Requests\ERP\Product\ProductUpdatePriorityRequest;
use App\Http\Requests\ERP\Product\ProductUpdateRequest;
use App\Http\Requests\ERP\Product\ProductVariantStoreRequest;
use App\Http\Requests\ERP\Product\ProductVariantUpdateRequest;
use App\Http\Requests\ERP\Product\ProductVariationsFetchRequest;
use App\Http\Requests\ERP\Product\PublishDraftAsActualRequest;
use App\Http\Requests\ERP\Product\TranslateAIRequest;
use App\Services\ERP\Catalog\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    private ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    // main
    public function fetch(ProductFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(ProductFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function autocomplete(ProductAutocompleteRequest $request): JsonResponse
    {
        try {
            return $this->service->autocomplete($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(ProductInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function clone(ProductCloneRequest $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        try {
            return $this->service->clone($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function publishDraftAsActual(PublishDraftAsActualRequest $request): JsonResponse
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        try {
            return $this->service->publishDraftAsActual($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function bulkDelete(ProductBulkDeleteRequest $request): JsonResponse
    {
        try {
            return $this->service->bulkDelete($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(ProductUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated(), $request);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('products-errors')->error('translateAI', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function updatePriority(ProductUpdatePriorityRequest $request): JsonResponse
    {
        try {
            return $this->service->updatePriority($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('products-errors')->error('approveTranslation', ['error' => $exception]);

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
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function forceDeleteDraft($id): JsonResponse
    {
        try {
            return $this->service->forceDeleteDraft((int)$id);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    // translation
    public function deleteTranslation($id): JsonResponse
    {
        try {
            return $this->service->deleteTranslation((int)$id);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    // variation
    public function fetchVariations(ProductVariationsFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchVariations($request->validated());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function storeVariant(ProductVariantStoreRequest $request): JsonResponse
    {
        try {
            return $this->service->storeVariant($request->all(), $request);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function deleteVariant($id): JsonResponse
    {
        try {
            return $this->service->deleteVariant((int)$id);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function deleteVariants($productId, $deleteInvalids): JsonResponse
    {
        try {
            return $this->service->deleteVariants((int)$productId, filter_var($deleteInvalids, FILTER_VALIDATE_BOOLEAN));
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function generateVariations(ProductGenerateVariationsRequest $request): JsonResponse
    {
        try {
            return $this->service->generateVariations($request->id, $request->sku, $request->generating_variations_attribute_values);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function updateVariant(ProductVariantUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->updateVariant($request->all(), $request);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function uploadVariations(Request $request): JsonResponse
    {
        try {
            return $this->service->uploadVariations($request->all(), $request);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function exportVariations(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            ini_set('memory_limit', -1);
            ini_set('max_execution_time', 0);
            return $this->service->exportVariations($request->all());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function feed(string $vendor, string $language, string $currency, string $category_slug, int $category_id, string $file_name)
    {
        try {
            return $this->service->generateFeed($vendor, $language, $currency, $category_id, $file_name);
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Detect needed attribute values failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchVariantTieredPrices(Request $request): JsonResponse
    {
        try {
            return $this->service->fetchVariantTieredPrices($request->all());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function updateVariantTieredPrices(Request $request): JsonResponse
    {
        try {
            return $this->service->updateVariantTieredPrices($request->all());
        } catch (\Exception $exception) {
            Log::channel('products-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
