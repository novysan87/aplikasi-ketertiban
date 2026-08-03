<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Satu akun = satu sesi aktif.
 *
 * Jika user sudah login di perangkat lain (active_session_token berbeda),
 * sesi ini dianggap tidak sah → logout otomatis + arahkan ke halaman login.
 */
class SingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $token = $user->active_session_token;

        if ($token && $token !== Session::getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Sesi berakhir: akun ini sedang digunakan di perangkat lain.'])
                ->onlyInput('username');
        }

        return $next($request);
    }
}
