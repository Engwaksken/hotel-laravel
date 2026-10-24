<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HotelFeatureSetting
{
    private const TABLE = 'hotel_feature_settings';

    public static function get(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable(self::TABLE)) {
            return $default;
        }

        $row = DB::table(self::TABLE)
            ->where('key', $key)
            ->first();

        if (! $row) {
            return $default;
        }

        if ((bool) $row->is_secret) {
            try {
                return Crypt::decryptString((string) $row->value);
            } catch (Throwable) {
                return $default;
            }
        }

        return $row->value ?? $default;
    }

    public static function put(
        string $key,
        mixed $value,
        bool $secret = false
    ): void {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        $stored = $value;

        if (is_bool($stored)) {
            $stored = $stored ? '1' : '0';
        } elseif (is_array($stored) || is_object($stored)) {
            $stored = json_encode(
                $stored,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } elseif ($stored !== null) {
            $stored = (string) $stored;
        }

        if ($secret && $stored !== null && $stored !== '') {
            $stored = Crypt::encryptString($stored);
        }

        DB::table(self::TABLE)->updateOrInsert(
            ['key' => $key],
            [
                'value' => $stored,
                'is_secret' => $secret,
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
