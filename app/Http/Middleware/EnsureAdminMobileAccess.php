<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan request datang dari admin (`users`) yang punya akses aplikasi admin.
 * Token app admin diterbitkan untuk model User; tolak bila bukan admin / akses dicabut.
 */
class EnsureAdminMobileAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->canAccessMobileAdmin()) {
            $user?->currentAccessToken()?->delete();

            return response()->json([
                'success' => false,
                'message' => 'Akses admin tidak valid atau dicabut.',
                'code' => 'admin_access_revoked',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}
