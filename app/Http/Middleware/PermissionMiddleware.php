<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = null): Response
    {
        if (Auth::check() && Auth::user()->hasPermission($guard)) {
            return $next($request);
        }

        if (!$request->expectsJson()) {
            return abort(403);
        } else {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke halaman ini.'
            ], 403);
        }
    }
}
