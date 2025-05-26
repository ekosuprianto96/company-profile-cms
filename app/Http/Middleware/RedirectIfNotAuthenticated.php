<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAuthenticated extends Authenticate
{
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('admin.auth.login'); // Ganti dengan route login yang diinginkan
        }
    }
}
