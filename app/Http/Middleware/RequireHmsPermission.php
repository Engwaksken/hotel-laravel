<?php

namespace App\Http\Middleware;

use App\Support\HmsPermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireHmsPermission
{
    public function __construct(private HmsPermissionResolver $permissions) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        abort_unless($user && $this->permissions->allows($user, $permission), 403, 'You do not have permission to perform this action.');

        return $next($request);
    }
}
