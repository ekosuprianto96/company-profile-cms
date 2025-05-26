<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Repositories\UserRepositorie;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateController extends Controller
{
    use AdminView;

    protected $user;

    public function __construct(
        UserRepositorie $user
    ) {
        $this->user = $user;
        $this->setView('admin.pages.auth.');
    }

    public function login()
    {
        return $this->view('login');
    }

    public function check(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return redirect()->intended('admin/dashboard');
            }

            throw new \Exception('Email atau password salah.');
        } catch (\Exception $err) {
            return redirect()->back()->withErrors([
                'message' => $err->getMessage()
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login');
        } catch (\Exception $err) {
            return redirect()->back()->withErrors([
                'message' => $err->getMessage()
            ]);
        }
    }
}
