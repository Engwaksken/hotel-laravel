<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\NewUserLoginDetailsNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAccountProvisioningService
{
    /**
     * Create a hotel user and send first-login credentials.
     *
     * The temporary password is kept only in memory long enough to hash it
     * and dispatch the notification. It is never stored in plain text.
     */
    public function create(array $attributes, ?string $temporaryPassword = null): User
    {
        $temporaryPassword = $temporaryPassword ?: $this->generateTemporaryPassword();

        $user = DB::transaction(function () use ($attributes, $temporaryPassword) {
            $attributes['password'] = Hash::make($temporaryPassword);
            $attributes['must_change_password'] = true;
            $attributes['credentials_sent_at'] = null;

            return User::create($attributes);
        });

        $propertyName = null;

        if ($user->property_id && method_exists($user, 'property')) {
            $propertyName = $user->property?->name;
        }

        $user->notify(new NewUserLoginDetailsNotification(
            temporaryPassword: $temporaryPassword,
            role: $user->role,
            propertyName: $propertyName,
        ));

        $user->forceFill([
            'credentials_sent_at' => now(),
        ])->saveQuietly();

        return $user;
    }

    public function resend(User $user): string
    {
        $temporaryPassword = $this->generateTemporaryPassword();

        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'credentials_sent_at' => null,
        ])->save();

        $propertyName = null;

        if ($user->property_id && method_exists($user, 'property')) {
            $propertyName = $user->property?->name;
        }

        $user->notify(new NewUserLoginDetailsNotification(
            temporaryPassword: $temporaryPassword,
            role: $user->role,
            propertyName: $propertyName,
        ));

        $user->forceFill([
            'credentials_sent_at' => now(),
        ])->saveQuietly();

        return $temporaryPassword;
    }

    private function generateTemporaryPassword(): string
    {
        return Str::upper(Str::random(2))
            .Str::lower(Str::random(4))
            .random_int(100, 999)
            .'!'
            .Str::random(3);
    }
}
