<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\User\UserAutocompleteRequest;
use App\Http\Requests\ERP\User\UserFetchByFieldRequest;
use App\Http\Requests\ERP\User\UserFetchRequest;
use App\Http\Requests\ERP\User\UserInsertRequest;
use App\Http\Requests\ERP\User\UserInterestsRequest;
use App\Http\Requests\ERP\User\UserUpdateRequest;
use App\Services\ERP\Users\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class UserController extends Controller
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function fetch(UserFetchRequest $request): JsonResponse
    {
        try {
            return $this->service->fetch($request->validated());
        } catch (\Exception $exception) {
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function fetchByField(UserFetchByFieldRequest $request): JsonResponse
    {
        try {
            return $this->service->fetchByField($request->validated());
        } catch (\Exception $exception) {
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function insert(UserInsertRequest $request): JsonResponse
    {
        try {
            return $this->service->insert($request->validated());
        } catch (\Exception $exception) {
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        try {
            return $this->service->update($request->validated());
        } catch (\Exception $exception) {
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function interests(UserInterestsRequest $request): JsonResponse
    {
        try {
            return $this->service->interests($request->validated());
        } catch (\Exception $exception) {
            Log::channel('users-errors')->error('interests', ['error' => $exception]);

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
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }

    public function autocomplete(UserAutocompleteRequest $request): JsonResponse
    {
        try {
            return $this->service->autocomplete($request->validated());
        } catch (\Exception $exception) {
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

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
            Log::channel('users-errors')->error('Failed', ['error' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed!',
                'error' => $exception->getMessage()
            ], ($exception->getCode() ?: 400));
        }
    }
}
