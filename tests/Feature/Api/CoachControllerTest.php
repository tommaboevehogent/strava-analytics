<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_insight_requires_authentication(): void
    {
        $this->getJson('/api/coach/weekly-insight')->assertUnauthorized();
    }

    public function test_weekly_insight_returns_features_and_llm_response(): void
    {
        $user = User::factory()->create();
        Activity::factory()->create(['started_at' => now()->subDay()]);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => json_encode([
                        'summary' => 'Solide week, geen alarmsignalen.',
                        'risk_level' => 'low',
                        'recommendation' => 'Bouw rustig verder op.',
                    ])],
                ],
            ]),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/coach/weekly-insight');

        $response->assertOk();
        $response->assertJsonPath('insight.risk_level', 'low');
        $response->assertJsonStructure([
            'features',
            'insight' => ['summary', 'risk_level', 'recommendation'],
            'generated_at',
        ]);
    }

    public function test_weekly_insight_is_cached_and_only_calls_the_llm_once(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => json_encode([
                    'summary' => 'ok', 'risk_level' => 'low', 'recommendation' => 'ok',
                ])]],
            ]),
        ]);

        $this->actingAs($user, 'sanctum')->getJson('/api/coach/weekly-insight')->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/coach/weekly-insight')->assertOk();

        Http::assertSentCount(1);
    }
}
