<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\MarketplaceAuth\MarketplaceAuthRedirectUrlRequest;
use App\Services\ERP\Marketplaces\MarketplaceAuth\MarketplaceAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MarketplaceAuthController extends Controller
{

    private MarketplaceAuthService $service;

    public function __construct(MarketplaceAuthService $service)
    {
        $this->service = $service;
    }

    public function redirect(MarketplaceAuthRedirectUrlRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            return response()->json([
                'url' => $this->service->redirect($data),
                'success' => true,
                'message' => 'Successfully updated!'
            ]);
        } catch (\Exception $exception) {
            Log::channel('marketplace-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function handleCallback($vendor, $marketplace, Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->all();
        $data['vendor'] = $vendor;
        $data['marketplace'] = $marketplace;

        try {
            return $this->service->callback($data);
        } catch (\Exception $exception) {
            Log::channel('marketplace-settings-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

}
