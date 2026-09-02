<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Carbon;

/**
 * Turns raw activity rows into the handful of trend/load numbers that
 * actually mean something for training advice. An LLM (or a human coach)
 * reasons far better over "ACWR 1.6" than over fifty raw activity rows —
 * this is the feature-engineering step, kept separate from the LLM call
 * itself so it stays independently testable and reusable.
 */
class TrainingFeatures
{
    public function weeklySnapshot(): array
    {
        $acuteMinutes = $this->totalMinutes(now()->subDays(7), now());
        $chronicMinutes = $this->totalMinutes(now()->subDays(28), now()) / 4;

        return [
            'acute_load_min' => round($acuteMinutes, 1),
            'chronic_load_min' => round($chronicMinutes, 1),
            'acwr' => $chronicMinutes > 0 ? round($acuteMinutes / $chronicMinutes, 2) : null,
            'efficiency_trend_pct' => $this->efficiencyTrend(),
            'rest_days_last_7' => $this->restDaysInLastDays(7),
            'longest_current_rest_streak_days' => $this->longestCurrentRestStreak(),
            'activity_count_last_7' => Activity::query()
                ->whereBetween('started_at', [now()->subDays(7), now()])
                ->count(),
        ];
    }

    private function totalMinutes(Carbon $from, Carbon $to): float
    {
        return Activity::query()
            ->whereBetween('started_at', [$from, $to])
            ->sum('moving_time_s') / 60;
    }

    /**
     * % change in "speed per heartbeat" (average_speed_ms / average_heartrate)
     * between the last 7 days and the same 7-day window four weeks ago.
     * Positive means you're covering more ground for the same effort — a
     * simple fitness-trend proxy that doesn't need a max-HR/resting-HR
     * profile the way a proper TRIMP score would.
     */
    private function efficiencyTrend(): ?float
    {
        $recent = $this->averageEfficiency(now()->subDays(7), now());
        $baseline = $this->averageEfficiency(now()->subDays(35), now()->subDays(28));

        if ($recent === null || $baseline === null || $baseline == 0.0) {
            return null;
        }

        return round((($recent - $baseline) / $baseline) * 100, 1);
    }

    private function averageEfficiency(Carbon $from, Carbon $to): ?float
    {
        $activities = Activity::query()
            ->whereBetween('started_at', [$from, $to])
            ->whereNotNull('average_heartrate')
            ->where('average_heartrate', '>', 0)
            ->get();

        if ($activities->isEmpty()) {
            return null;
        }

        return $activities->avg(fn (Activity $a) => $a->average_speed_ms / $a->average_heartrate);
    }

    private function restDaysInLastDays(int $days): int
    {
        $activeDays = Activity::query()
            ->whereBetween('started_at', [now()->subDays($days), now()])
            ->get()
            ->map(fn (Activity $a) => $a->started_at->toDateString())
            ->unique();

        return $days - $activeDays->count();
    }

    private function longestCurrentRestStreak(): int
    {
        $lastActivity = Activity::query()->latest('started_at')->first();

        return $lastActivity ? (int) now()->diffInDays($lastActivity->started_at, true) : 0;
    }
}
