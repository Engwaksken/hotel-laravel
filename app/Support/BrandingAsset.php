<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

final class BrandingAsset
{
    public static function url(?string $path): ?string
    {
        if (! $path || trim($path) === '') {
            return null;
        }

        $path = trim($path);
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        while (str_starts_with($path, 'public/') || str_starts_with($path, 'storage/')) {
            $path = preg_replace('#^(public/|storage/)#', '', $path);
        }

        try {
            $disk = Storage::disk('public');
            $url = $disk->url($path);

            if ($disk->exists($path)) {
                $url .= (str_contains($url, '?') ? '&' : '?').'v='.$disk->lastModified($path);
            }

            return $url;
        } catch (Throwable) {
            return asset('storage/'.$path);
        }
    }

    public static function mimeType(?string $path): string
    {
        return match (strtolower(pathinfo((string) $path, PATHINFO_EXTENSION))) {
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/x-icon',
        };
    }
}
