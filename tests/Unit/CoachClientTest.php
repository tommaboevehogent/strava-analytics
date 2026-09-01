<?php

namespace Tests\Unit;

use App\Services\CoachClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachClientTest extends TestCase
{
    public function test_it_parses_a_response_wrapped_in_a_markdown_code_fence(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => "```json\n".json_encode([
                        'summary' => 'ok',
                        'risk_level' => 'low',
                        'recommendation' => 'ok',
                    ])."\n```"],
                ],
            ]),
        ]);

        $insight = (new CoachClient)->weeklyInsight(['acwr' => 1.0]);

        $this->assertSame('ok', $insight['summary']);
        $this->assertSame('low', $insight['risk_level']);
    }

    public function test_it_parses_a_plain_json_response_without_a_code_fence(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => json_encode([
                        'summary' => 'ok',
                        'risk_level' => 'low',
                        'recommendation' => 'ok',
                    ])],
                ],
            ]),
        ]);

        $insight = (new CoachClient)->weeklyInsight(['acwr' => 1.0]);

        $this->assertSame('ok', $insight['summary']);
    }
}
