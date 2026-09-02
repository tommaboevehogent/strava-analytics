<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/trainingen')->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_their_activities(): void
    {
        $user = User::factory()->create();
        Activity::factory()->create(['name' => 'Middagloop', 'type' => 'Run']);

        $this->actingAs($user)
            ->get('/trainingen')
            ->assertOk()
            ->assertSee('Middagloop');
    }

    public function test_activities_can_be_filtered_by_type(): void
    {
        $user = User::factory()->create();
        Activity::factory()->create(['name' => 'Een looprondje', 'type' => 'Run']);
        Activity::factory()->create(['name' => 'Een fietsrit', 'type' => 'Ride']);

        $response = $this->actingAs($user)->get('/trainingen?type=Ride');

        $response->assertOk();
        $response->assertSee('Een fietsrit');
        $response->assertDontSee('Een looprondje');
    }

    public function test_authenticated_user_can_view_an_activity_detail(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create(['name' => 'Lange duurloop']);

        $this->actingAs($user)
            ->get("/trainingen/{$activity->id}")
            ->assertOk()
            ->assertSee('Lange duurloop');
    }
}
