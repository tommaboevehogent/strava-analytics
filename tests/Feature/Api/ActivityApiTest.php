<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_activities_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/activities')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_activities(): void
    {
        $user = User::factory()->create();
        Activity::factory()->count(3)->create(['type' => 'Run']);
        Activity::factory()->create(['type' => 'Ride']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/activities?type=Run');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_filter_activities_by_date_range(): void
    {
        $user = User::factory()->create();
        Activity::factory()->create(['started_at' => now()->subMonths(2)]);
        $recent = Activity::factory()->create(['started_at' => now()->subDay()]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/activities?from='.now()->subWeek()->toDateString());

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($recent->id, $response->json('data.0.id'));
    }
}
