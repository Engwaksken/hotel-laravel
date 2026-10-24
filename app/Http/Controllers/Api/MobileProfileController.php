<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MobileProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ];

        if (Schema::hasColumn('users', 'phone')) {
            $rules['phone'] = [
                'nullable',
                'string',
                'max:50',
            ];
        }

        $validated = $request->validate($rules);

        $user->fill($validated);
        $user->save();

        if (method_exists($user, 'property')) {
            $user->loadMissing('property');
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => (int) $user->id,
                'name' => (string) ($user->name ?? ''),
                'email' => (string) ($user->email ?? ''),
                'phone' => Schema::hasColumn('users', 'phone')
                    ? $user->phone
                    : null,
                'role' => (string) ($user->role ?? 'staff'),
                'property_id' => $user->property_id ?? null,
                'property_name' => $user->property?->name ?? null,
            ],
        ]);
    }
}
