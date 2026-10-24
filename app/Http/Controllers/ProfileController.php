<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => ['nullable', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        foreach ([
            'phone' => 50,
            'job_title' => 120,
            'address' => 500,
        ] as $column => $max) {
            if (Schema::hasColumn('users', $column)) {
                $rules[$column] = ['nullable', 'string', 'max:'.$max];
            }
        }

        $data = $request->validate($rules);

        $payload = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
        ];

        foreach (['phone', 'job_title', 'address'] as $column) {
            if (
                Schema::hasColumn('users', $column)
                && array_key_exists($column, $data)
            ) {
                $payload[$column] = filled($data[$column])
                    ? trim((string) $data[$column])
                    : null;
            }
        }

        if (filled($data['password'] ?? null)) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->forceFill($payload)->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
