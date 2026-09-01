<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets the logged-in owner manage their own Sanctum API tokens through a
 * web UI, instead of `php artisan tinker`. Scoped to $request->user() on
 * every query, so even though this app only ever has one user, revoking
 * never touches a token that isn't yours.
 */
class ApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        return view('tokens.index', [
            'tokens' => $request->user()->tokens()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        // Sanctum only ever returns the plaintext token once, at creation —
        // only its hash is stored. Flash it for the very next request only.
        $token = $request->user()->createToken($data['name']);

        return redirect()
            ->route('tokens.index')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return redirect()->route('tokens.index');
    }
}
