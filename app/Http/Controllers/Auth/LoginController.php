<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Single session: tolak login bila akun masih aktif di perangkat lain
            if ($user->sessionTokenIsAlive()) {
                Auth::logout();

                return back()->withErrors([
                    'username' => 'Akun ini sedang digunakan di perangkat lain. Silakan logout dari perangkat tersebut terlebih dahulu.',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();
            $user->forceFill(['active_session_token' => $request->session()->getId()])->save();

            // Role 'other' langsung ke presensi, bukan dashboard
            if ($user->role === 'other') {
                return redirect()->route('attendances.index');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->active_session_token === $request->session()->getId()) {
            $user->forceFill(['active_session_token' => null])->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
