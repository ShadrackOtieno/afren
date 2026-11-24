<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Usage: ->middleware('role:finance,recruiter'). super_admin always passes. */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        abort_unless($user && $user->is_active && ($user->role === 'super_admin' || in_array($user->role, $roles, true)), 403);
        return $next($request);
    }
}
