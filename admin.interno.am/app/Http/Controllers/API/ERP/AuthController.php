<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\Auth\LoginRequest;
use App\Services\ERP\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

//    public function register(RegisterRequest $request): JsonResponse
//    {
//        return $this->service->create($request->all());
//    }

    public function fetch(Request $request): JsonResponse
    {
        try {
            return $this->service->fetch();
        } catch (\Exception $exception) {
            Log::channel('auth-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            return $this->service->login($request->validated());
        } catch (\Exception $exception) {
            Log::channel('auth-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json('Logged out successfully');
        } catch (\Exception $exception) {
            Log::channel('auth-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function switch(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->service->switch($request->vendor);
        } catch (\Exception $exception) {
            Log::channel('auth-errors')->error('switch', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' =>  'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
