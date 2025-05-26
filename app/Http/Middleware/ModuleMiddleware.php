<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ModuleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check() && !$request->expectsJson()) {
            $menus = Auth::user()->role->menus ?? collect([]);
            if (!in_array($request->path(), $menus->pluck('url')->toArray())) {
                return redirect()->back()->withErrors([
                    'unauthorized' => 'Anda tidak memiliki akses ke halaman ini.'
                ]);
            }
        }

        return $next($request);
    }
}
