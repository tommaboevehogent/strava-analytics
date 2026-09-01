<?php

namespace App\Http\Controllers;

use App\Services\StravaClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * One-time OAuth handshake to authorize this app against your own Strava
 * account. Visit /strava/connect once after deploying, approve the app on
 * Strava, and the callback stores the token pair — nothing else in the app
 * touches these routes again (the sync command/webhook use the stored token
 * and refresh it automatically).
 */
class StravaAuthController extends Controller
{
    public function __construct(private StravaClient $strava) {}

    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(32);
        $request->session()->put('strava_oauth_state', $state);

        return redirect($this->strava->buildAuthorizeUrl($state));
    }

    public function callback(Request $request): string
    {
        if ($request->get('error')) {
            return 'Strava authorization was denied. Nothing was stored.';
        }

        $expectedState = $request->session()->pull('strava_oauth_state');

        abort_unless(
            $request->get('state') && hash_equals($expectedState ?? '', $request->get('state')),
            403,
            'Invalid OAuth state.'
        );

        $token = $this->strava->exchangeCodeForToken($request->get('code'));

        return "Connected. Athlete ID {$token->athlete_id} stored — you can close this tab.";
    }
}
