<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\TrainingFeatures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TrainingFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_acwr_is_ratio_of_acute_to_chronic_load(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 12:00:00'));

        // Acute window (last 7 days): one 180-minute session.
        Activity::factory()->create(['started_at' => now()->subDays(2), 'moving_time_s' => 10800]);
        // Chronic window (last 28 days) also includes three 60-minute
        // sessions from further back, one per prior week.
        Activity::factory()->create(['started_at' => now()->subDays(10), 'moving_time_s' => 3600]);
        Activity::factory()->create(['started_at' => now()->subDays(17), 'moving_time_s' => 3600]);
        Activity::factory()->create(['started_at' => now()->subDays(24), 'moving_time_s' => 3600]);

        $snapshot = (new TrainingFeatures)->weeklySnapshot();

        // acute = 180 min; chronic = (180 + 60 + 60 + 60) / 4 = 90 min.
        $this->assertSame(180.0, $snapshot['acute_load_min']);
        $this->assertSame(90.0, $snapshot['chronic_load_min']);
        $this->assertSame(2.0, $snapshot['acwr']);
    }

    public function test_acwr_is_null_without_any_training_history(): void
    {
        $snapshot = (new TrainingFeatures)->weeklySnapshot();

        $this->assertNull($snapshot['acwr']);
    }

    public function test_rest_days_counts_days_without_an_activity_in_the_last_week(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 12:00:00'));

        Activity::factory()->create(['started_at' => now()->subDays(1)]);
        Activity::factory()->create(['started_at' => now()->subDays(3)]);

        $snapshot = (new TrainingFeatures)->weeklySnapshot();

        $this->assertSame(5, $snapshot['rest_days_last_7']);
    }
}
