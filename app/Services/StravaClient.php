<?php

namespace App\Services;

use App\Models\StravaToken;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin wrapper around the Strava v3 API: OAuth token exchange/refresh and
 * the two endpoints this app needs (list activities, get one activity).
 *
 * Strava rate limits: 200 requests / 15 min, 2000 requests / day (default
 * app tier). We read the response headers and back off instead of hammering
 * an already-throttled window — cheap insurance for a personal project that
 * mostly runs unattended via the scheduler/webhook.
 */
class StravaClient
{
    private const BASE_URL = 'https://www.strava.com/api/v3';

    public function buildAuthorizeUrl(string $state): string
    {
        return 'https://www.strava.com/oauth/authorize?'.http_build_query([
            'client_id' => config('services.strava.client_id'),
            'redirect_uri' => config('services.strava.redirect_uri'),
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'read,activity:read_all',
            'state' => $state,
        ]);
    }

    public function exchangeCodeForToken(string $code): StravaToken
    {
        $response = Http::asForm()->post('https://www.strava.com/oauth/token', [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ])->throw();

        return $this->storeToken($response->json());
    }

    /**
     * Return a valid access token for the given athlete, refreshing it
     * first if it has expired (or is about to).
     */
    public function accessTokenFor(int $athleteId): string
    {
        $token = StravaToken::where('athlete_id', $athleteId)->firstOrFail();

        if ($token->isExpired()) {
            $token = $this->refreshToken($token);
        }

        return $token->access_token;
    }

    public function refreshToken(StravaToken $token): StravaToken
    {
        $response = Http::asForm()->post('https://www.strava.com/oauth/token', [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
            'refresh_token' => $token->refresh_token,
            'grant_type' => 'refresh_token',
        ])->throw();

        return $this->storeToken($response->json());
    }

    private function storeToken(array $payload): StravaToken
    {
        return StravaToken::updateOrCreate(
            ['athlete_id' => $payload['athlete']['id'] ?? $payload['athlete_id']],
            [
                'access_token' => $payload['access_token'],
                'refresh_token' => $payload['refresh_token'],
                'expires_at' => now()->createFromTimestamp($payload['expires_at']),
                'scope' => explode(',', $payload['scope'] ?? ''),
            ]
        );
    }

    /**
     * Paginated activity list. Strava caps page size at 200.
     *
     * @return array<int, array>
     */
    public function listActivities(int $athleteId, ?int $after = null, int $page = 1, int $perPage = 100): array
    {
        return $this->request($athleteId, 'GET', '/athlete/activities', array_filter([
            'after' => $after,
            'page' => $page,
            'per_page' => $perPage,
        ]));
    }

    public function getActivity(int $athleteId, int $stravaActivityId): array
    {
        return $this->request($athleteId, 'GET', "/activities/{$stravaActivityId}");
    }

    private function request(int $athleteId, string $method, string $path, array $query = []): array
    {
        $token = $this->accessTokenFor($athleteId);

        $response = Http::withToken($token)
            ->baseUrl(self::BASE_URL)
            ->send($method, $path, ['query' => $query]);

        $this->logRateLimitIfClose($response);

        if ($response->status() === 429) {
            Log::warning('Strava rate limit hit', ['path' => $path]);
            throw new RuntimeException('Strava rate limit exceeded, try again later.');
        }

        return $response->throw()->json() ?? [];
    }

    private function logRateLimitIfClose($response): void
    {
        // Header format: "usage,limit" e.g. "50,200" for the 15-min window
        // followed by the daily window, comma-separated between the two.
        $usage = $response->header('X-RateLimit-Usage');
        $limit = $response->header('X-RateLimit-Limit');

        if (! $usage || ! $limit) {
            return;
        }

        [$shortUsage] = explode(',', $usage);
        [$shortLimit] = explode(',', $limit);

        if ($shortLimit > 0 && ($shortUsage / $shortLimit) > 0.8) {
            Log::notice('Approaching Strava 15-min rate limit', [
                'usage' => $usage,
                'limit' => $limit,
            ]);
        }
    }
}
