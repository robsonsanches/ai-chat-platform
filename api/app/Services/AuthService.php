<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data, Request $request): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $this->generateToken($user, $request);

        return [
            'user' => $user,
            ...$token,
        ];
    }

    public function login(array $data, Request $request): array
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas'],
            ]);
        }

        $token = $this->generateToken($user, $request);

        return [
            'user' => $user,
            ...$token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    private function generateToken(User $user, Request $request): array
    {
        $deviceName = $request->input('device_name')
            ?? $request->userAgent()
            ?? 'unknown';

        $remember = $request->boolean('remember');

        $expiresAt = $remember
            ? now()->addDays(30)
            : now()->addHours(6);

        $token = $user->createToken(
            $deviceName,
            ['*'],
            $expiresAt
        );

        return [
            'access_token' => $token->plainTextToken,
            'expires_at' => $expiresAt->toISOString(),
            'expires_in' => now()->diffInSeconds($expiresAt),
        ];
    }
}