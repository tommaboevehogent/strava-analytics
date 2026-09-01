<?php

namespace App\Http\Controllers;

use App\Jobs\SyncActivityJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Strava's webhook protocol (https://developers.strava.com/docs/webhooks/):
 *  1. GET  /webhooks/strava — one-time subscription validation. Echo the
 *     hub.challenge value back if hub.verify_token matches ours.
 *  2. POST /webhooks/strava — push events as they happen. Strava expects a
 *     response within 2 seconds, so we only queue a job here and return.
 */
class StravaWebhookController extends Controller
{
    public function verify(Request $request): JsonResponse|Response
    {
        // Strava sends hub.mode / hub.challenge / hub.verify_token, but PHP's
        // query-string parser turns dots in param names into underscores —
        // so on the receiving end these arrive as hub_mode, hub_challenge,
        // hub_verify_token. The JSON we echo back still uses the literal
        // "hub.challenge" key, since that's a response body key we control.
        if ($request->query('hub_mode') !== 'subscribe') {
            return response('', 400);
        }

        $verifyToken = $request->query('hub_verify_token');

        if (! hash_equals((string) config('services.strava.webhook_verify_token'), (string) $verifyToken)) {
            return response('', 403);
        }

        return response()->json(['hub.challenge' => $request->query('hub_challenge')]);
    }

    public function handle(Request $request): Response
    {
        $event = $request->all();

        // Only activity create/update events are useful here; deauth and
        // athlete events are ignored for this project's scope.
        if (($event['object_type'] ?? null) === 'activity'
            && in_array($event['aspect_type'] ?? null, ['create', 'update'], true)) {
            SyncActivityJob::dispatch(
                athleteId: (int) $event['owner_id'],
                stravaActivityId: (int) $event['object_id'],
            );
        }

        // Strava requires a fast 200 regardless of what we did with the event.
        return response('', 200);
    }
}
