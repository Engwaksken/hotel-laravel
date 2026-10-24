<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemSetting
{
    private const TABLE = 'system_settings';

    /**
     * Get a setting value.
     */
    public static function get(
        string $key,
        mixed $default = null
    ): mixed {
        if (! Schema::hasTable(self::TABLE)) {
            return $default;
        }

        if (
            ! Schema::hasColumn(self::TABLE, 'key')
            || ! Schema::hasColumn(self::TABLE, 'value')
        ) {
            return $default;
        }

        $value = DB::table(self::TABLE)
            ->where('key', $key)
            ->value('value');

        return $value !== null
            ? $value
            : $default;
    }

    /**
     * Store or update a setting.
     *
     * This method is intentionally schema-aware because older HMS
     * installations may only have key/value/timestamps while newer
     * installations may additionally have type, group, is_public, etc.
     */
    public static function put(
        string $key,
        mixed $value,
        ?string $type = null,
        ?bool $isPublic = null
    ): void {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        if (
            ! Schema::hasColumn(self::TABLE, 'key')
            || ! Schema::hasColumn(self::TABLE, 'value')
        ) {
            return;
        }

        $exists = DB::table(self::TABLE)
            ->where('key', $key)
            ->exists();

        $payload = [
            'value' => self::normaliseValue($value),
        ];

        if (
            $type !== null
            && Schema::hasColumn(self::TABLE, 'type')
        ) {
            $payload['type'] = $type;
        }

        if (
            $isPublic !== null
            && Schema::hasColumn(self::TABLE, 'is_public')
        ) {
            $payload['is_public'] = $isPublic;
        }

        if (
            Schema::hasColumn(self::TABLE, 'updated_at')
        ) {
            $payload['updated_at'] = now();
        }

        if ($exists) {
            DB::table(self::TABLE)
                ->where('key', $key)
                ->update($payload);

            return;
        }

        $insert = array_merge(
            ['key' => $key],
            $payload
        );

        if (
            Schema::hasColumn(self::TABLE, 'created_at')
        ) {
            $insert['created_at'] = now();
        }

        DB::table(self::TABLE)->insert($insert);
    }

    /**
     * Delete a setting.
     */
    public static function forget(
        string $key
    ): void {
        if (
            ! Schema::hasTable(self::TABLE)
            || ! Schema::hasColumn(self::TABLE, 'key')
        ) {
            return;
        }

        DB::table(self::TABLE)
            ->where('key', $key)
            ->delete();
    }

    /**
     * Return all settings as key => value.
     */
    public static function all(): array
    {
        if (
            ! Schema::hasTable(self::TABLE)
            || ! Schema::hasColumn(self::TABLE, 'key')
            || ! Schema::hasColumn(self::TABLE, 'value')
        ) {
            return [];
        }

        return DB::table(self::TABLE)
            ->pluck('value', 'key')
            ->all();
    }

    private static function normaliseValue(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (
            is_array($value)
            || is_object($value)
        ) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );
        }

        return (string) $value;
    }
}
