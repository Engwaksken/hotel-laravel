<?php
namespace App\Http\Middleware; use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class EnsureHmsRole { public function handle(Request $request, Closure $next, string ...$roles):Response { $user=$request->user(); abort_unless($user && $user->is_active,403,'Your account is inactive.'); if($roles && !in_array($user->role,$roles,true)) abort(403,'You do not have permission to access this area.'); return $next($request); } }
