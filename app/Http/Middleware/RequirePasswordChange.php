<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if (
            $request->routeIs('password.first-login.*')
            || $request->routeIs('logout')
        ) {
            return $next($request);
        }

        return redirect()
            ->route('password.first-login.edit')
            ->with('warning', 'Please change your temporary password before continuing.');
    }
}
