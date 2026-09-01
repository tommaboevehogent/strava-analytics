<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_stats_aggregate_this_weeks_activities(): void
    {
        $user = User::factory()->create();

        Activity::factory()->create([
            'started_at' => now()->startOfWeek()->addDay(),
            'distance_m' => 10_000,
            'moving_time_s' => 3600,
        ]);
        Activity::factory()->create([
            'started_at' => now()->startOfWeek()->addDays(2),
            'distance_m' => 5_000,
            'moving_time_s' => 1800,
        ]);
        // Outside the current week — must not be counted.
        Activity::factory()->create(['started_at' => now()->subWeeks(2)]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/stats/weekly');

        $response->assertOk();
        $response->assertJson([
            'activity_count' => 2,
            'distance_km' => 15.0,
            'moving_time_h' => 1.5,
        ]);
    }

    public function test_trends_returns_requested_number_of_weeks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/stats/trends?weeks=4');

        $response->assertOk();
        $this->assertCount(4, $response->json('weeks'));
    }
}
