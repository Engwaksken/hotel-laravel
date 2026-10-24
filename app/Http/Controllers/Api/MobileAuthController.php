<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($validated['email']))])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided login details are incorrect.'],
            ]);
        }

        if (isset($user->is_active) && ! (bool) $user->is_active) {
            return response()->json([
                'message' => 'Your account is inactive. Please contact the hotel administrator.',
            ], 403);
        }

        $deviceName = trim((string) ($validated['device_name'] ?? 'Hotel HMS Mobile'));

        // Prevent unlimited duplicate tokens for the same device label.
        $user->tokens()
            ->where('name', $deviceName)
            ->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        if (method_exists($user, 'property')) {
            $user->loadMissing('property');
        }

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->payload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (method_exists($user, 'property')) {
            $user->loadMissing('property');
        }

        return response()->json([
            'user' => $this->payload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function payload(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => (string) ($user->name ?? $user->full_name ?? 'User'),
            'email' => (string) $user->email,
            'role' => (string) ($user->role ?? 'staff'),
            'property_id' => $user->property_id ?? null,
            'property_name' => $user->property?->name ?? null,
            'must_change_password' => (bool) ($user->must_change_password ?? false),
        ];
    }
}
