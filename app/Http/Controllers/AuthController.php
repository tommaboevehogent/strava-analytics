<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Plain session-based login (separate from the Sanctum API tokens).
 * This app has exactly one intended user — you, the owner — so there's
 * no registration flow, just a login form for the account you create
 * yourself via `php artisan tinker`.
 */
class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Die combinatie van e-mail en wachtwoord klopt niet.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('tokens.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
