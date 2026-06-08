<?php

namespace App\Services\ERP\Auth;

use App\Repositories\User\UserRepository;
use App\Services\ERP\Auth\Interfaces\AuthServiceInterface;
use App\Services\General\PasswordHash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    private UserRepository $repository;
    private PasswordHash $wpHasher;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
        $this->wpHasher = new PasswordHash(8, true);
    }

    public function create(array $data): JsonResponse
    {
        $user = $this->repository->create($data);
        $token = $user->createToken(config('app.token_name'))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Successfully registered!',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function login(array $data): JsonResponse
    {
        $user = $this->repository->fetchInfoByField('email', $data['email'], 'id, password, wc_password');

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid email or password!',
            ], 401);
        }

        if (Hash::check($data['password'], $user->password)) {
            goto LoggedIn;
        } else if ($this->wpHasher->CheckPassword($data['password'], $user->wc_password)) {

            $user->update([
                'password' => Hash::make($data['password']),
                'wc_password' => null
            ]);

            goto LoggedIn;
        }

        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'Invalid email or password!',
        ], 401);

        LoggedIn:

        $user = $this->repository->fetch($user->id);

        $token = $user->createToken(config('app.token_name'))->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'vendor_key' => $data['vendor_key'],
        ]);
    }

    public function switch(string $neededVendor): JsonResponse
    {
        $userEmail = Auth::user()->email;
        setDBConnection($neededVendor);

        $user = $this->repository->fetchInfoByField('email', $userEmail, 'id');
        $token = $user->createToken(config('app.token_name'))->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'vendor_key' => $neededVendor,
        ]);
    }

    public function fetch(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $this->repository->fetch(),
        ]);
    }
}
