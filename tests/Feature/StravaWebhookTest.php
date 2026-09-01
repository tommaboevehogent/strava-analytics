<?php

namespace Tests\Feature;

use App\Jobs\SyncActivityJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StravaWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_challenge_is_echoed_back_when_token_matches(): void
    {
        config(['services.strava.webhook_verify_token' => 'secret-token']);

        $response = $this->get('/webhooks/strava?'.http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'secret-token',
            'hub.challenge' => 'abc123',
        ]));

        $response->assertOk();
        $response->assertJson(['hub.challenge' => 'abc123']);
    }

    public function test_subscription_challenge_is_rejected_when_token_does_not_match(): void
    {
        config(['services.strava.webhook_verify_token' => 'secret-token']);

        $response = $this->get('/webhooks/strava?'.http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'wrong-token',
            'hub.challenge' => 'abc123',
        ]));

        $response->assertForbidden();
    }

    public function test_activity_create_event_dispatches_sync_job(): void
    {
        Bus::fake();

        $this->postJson('/webhooks/strava', [
            'object_type' => 'activity',
            'object_id' => 987654321,
            'aspect_type' => 'create',
            'owner_id' => 42,
        ])->assertOk();

        Bus::assertDispatched(SyncActivityJob::class, function (SyncActivityJob $job) {
            return $job->athleteId === 42 && $job->stravaActivityId === 987654321;
        });
    }

    public function test_non_activity_events_are_ignored(): void
    {
        Bus::fake();

        $this->postJson('/webhooks/strava', [
            'object_type' => 'athlete',
            'object_id' => 42,
            'aspect_type' => 'update',
            'owner_id' => 42,
        ])->assertOk();

        Bus::assertNotDispatched(SyncActivityJob::class);
    }
}
