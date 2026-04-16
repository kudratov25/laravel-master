<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use OpenApi\Attributes as OA;

class AuthController extends BaseController
{
    #[OA\Post(
        path: '/login',
        summary: 'Login and get JWT token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'test@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 422, description: 'Invalid credentials'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        if (!$token = Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Invalid email or password.', 422);
        }

        if (!Auth::user()->status) {
            Auth::logout();
            return $this->errorResponse('Your access is restricted. Contact the administrator.', 401);
        }

        return $this->respondWithToken($token);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Logout',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return $this->successResponse(null, 'Successfully logged out.');
    }

    #[OA\Post(
        path: '/refresh',
        summary: 'Refresh JWT token',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Token refreshed'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function refresh(): JsonResponse
    {
        $newToken = auth('api')->refresh();

        return $this->respondWithToken($newToken, 'Token refreshed.');
    }

    #[OA\Get(
        path: '/user',
        summary: 'Get authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'User data'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function user(): JsonResponse
    {
        $user = Auth::guard('api')->user()->load('roles', 'permissions', 'avatar');

        return $this->successResponse(UserResource::make($user));
    }

    #[OA\Put(
        path: '/change-password',
        summary: 'Change password',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['old_password', 'new_password', 'new_password_confirmation'],
                properties: [
                    new OA\Property(property: 'old_password', type: 'string'),
                    new OA\Property(property: 'new_password', type: 'string'),
                    new OA\Property(property: 'new_password_confirmation', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password changed'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->errorResponse('The current password is incorrect.', 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return $this->successResponse(null, 'Password changed successfully.');
    }

    #[OA\Post(
        path: '/forgot-password',
        summary: 'Send password reset link',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'test@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Reset link sent'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->errorResponse(__($status), 422);
        }

        return $this->successResponse(null, 'Password reset link sent to your email.');
    }

    #[OA\Post(
        path: '/reset-password',
        summary: 'Reset password with token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'token', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'password_confirmation', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password reset'),
            new OA\Response(response: 422, description: 'Invalid token or validation error'),
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->update(['password' => Hash::make($password)]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->errorResponse(__($status), 422);
        }

        return $this->successResponse(null, 'Password reset successfully.');
    }

    protected function respondWithToken(string $token, string $message = 'Login successful.'): JsonResponse
    {
        return $this->successResponse([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => UserResource::make(auth('api')->user()->load('roles', 'permissions')),
        ], $message);
    }
}
