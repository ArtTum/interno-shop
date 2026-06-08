<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserGroup;
use App\Repositories\User\UserRepository;
use App\Repositories\UserGroup\UserGroupRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckClientCertificate
{
    private UserRepository $userRepository;
    private UserGroupRepository $userGroupRepository;

    public function __construct(UserRepository $repository)
    {
        $this->userRepository = new UserRepository(new User());
        $this->userGroupRepository = new UserGroupRepository(new UserGroup());
    }

    public function handle(Request $request, Closure $next)
    {
        $sslVerify = $request->server('SSL_CLIENT_VERIFY');
        $sslCert = $request->server('SSL_CLIENT_CERT');
        $clientDN = $request->server('SSL_CLIENT_DN');

        $user = Auth::user();
        $email = $user ? $user->email : $request->email;


        $checkClientCertificate = $this->userRepository->getEmployeeUserCertificateStatusByEmail($email);

        if ($checkClientCertificate && $sslVerify !== 'SUCCESS') {
            if ($user) {
                $request->user()->currentAccessToken()->delete();
                return response()->json(['error' => 'Forbidden', 'logout' => true], 403);
            }
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing or Incorrect Client Certificate. Please contact your administrator.',
            ], 401);
        }
        $request->attributes->set('client_cert_verified', $sslVerify === 'SUCCESS');

        return $next($request);
    }
}
