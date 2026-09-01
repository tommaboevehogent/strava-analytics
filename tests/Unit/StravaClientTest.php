<?php

namespace Tests\Unit;

use App\Models\StravaToken;
use App\Services\StravaClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StravaClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_token_for_returns_stored_token_when_still_valid(): void
    {
        StravaToken::create([
            'athlete_id' => 1,
            'access_token' => 'valid-token',
            'refresh_token' => 'refresh-token',
            'expires_at' => now()->addHour(),
        ]);

        Http::fake(); // no requests should go out

        $token = (new StravaClient)->accessTokenFor(1);

        $this->assertSame('valid-token', $token);
        Http::assertNothingSent();
    }

    public function test_access_token_for_refreshes_an_expired_token(): void
    {
        StravaToken::create([
            'athlete_id' => 1,
            'access_token' => 'expired-token',
            'refresh_token' => 'refresh-token',
            'expires_at' => now()->subMinute(),
        ]);

        Http::fake([
            'https://www.strava.com/oauth/token' => Http::response([
                'athlete' => ['id' => 1],
                'access_token' => 'fresh-token',
                'refresh_token' => 'new-refresh-token',
                'expires_at' => now()->addHours(6)->timestamp,
                'scope' => 'read,activity:read_all',
            ]),
        ]);

        $token = (new StravaClient)->accessTokenFor(1);

        $this->assertSame('fresh-token', $token);

        $this->assertDatabaseHas('strava_tokens', [
            'athlete_id' => 1,
            'access_token' => 'fresh-token',
        ]);
    }

    public function test_list_activities_sends_pagination_and_after_params(): void
    {
        StravaToken::create([
            'athlete_id' => 1,
            'access_token' => 'valid-token',
            'refresh_token' => 'refresh-token',
            'expires_at' => now()->addHour(),
        ]);

        Http::fake([
            'www.strava.com/api/v3/athlete/activities*' => Http::response([
                ['id' => 111, 'name' => 'Morning trail run'],
            ]),
        ]);

        $activities = (new StravaClient)->listActivities(athleteId: 1, after: 1700000000, page: 2);

        $this->assertCount(1, $activities);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.strava.com/api/v3/athlete/activities?after=1700000000&page=2&per_page=100'
                && $request->hasHeader('Authorization', 'Bearer valid-token');
        });
    }
}
