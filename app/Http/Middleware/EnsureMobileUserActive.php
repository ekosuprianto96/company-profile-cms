<?php

namespace App\Http\Middleware;

use App\Models\MobileUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak request dari user mobile yang sedang diblokir/nonaktif meski masih
 * memegang token yang valid (pertahanan berlapis; ban juga mencabut token).
 */
class EnsureMobileUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof MobileUser && ! $user->canAccess()) {
            // Cabut token yang dipakai agar sesi benar-benar berakhir.
            $user->currentAccessToken()?->delete();

            $message = $user->isBanned()
                ? ($user->ban_reason ? 'Akun Anda diblokir: ' . $user->ban_reason : 'Akun Anda telah diblokir.')
                : 'Akun Anda sedang nonaktif.';

            return response()->json([
                'success' => false,
                'message' => $message,
                // Kode mesin: aplikasi mobile mengalihkan ke layar informasi blokir.
                'code' => $user->isBanned() ? 'account_blocked' : 'account_inactive',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}
