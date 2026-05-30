<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['string', 'max:255'],
        ]);

        $result = $this->authService->register($data, $request);

        return $this->success([
            ...$result,
            'user' => new UserResource($result['user']),
        ], 'Usuário registrado com sucesso', 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['string', 'max:255'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $result = $this->authService->login($data, $request);

        return $this->success([
            ...$result,
            'user' => new UserResource($result['user']),
        ], 'Login realizado com sucesso');
    }

    public function me(Request $request)
    {
        return $this->success(
            new UserResource($request->user()),
            'Usuário autenticado'
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->success(
            null,
            'Logout realizado com sucesso'
        );
    }

    public function logoutAll(Request $request)
    {
        $this->authService->logoutAll($request->user());

        return $this->success(
            null,
            'Logout em todos dispositivos'
        );
    }

    public function refresh(Request $request)
    {
        $result = $this->authService->refresh($request->user());

        return $this->success(
            $result,
            'Token renovado com sucesso'
        );
    }
}