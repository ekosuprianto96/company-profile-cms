<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi akses hanya untuk pengguna dengan peran superadmin.
 * Dipakai untuk halaman monitoring sistem (job, cron, kondisi server).
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(optional($request->user())->isSuperAdmin(), 403);

        return $next($request);
    }
}
