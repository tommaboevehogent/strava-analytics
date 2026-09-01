<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    /**
     * Totals for the current ISO week (Mon–Sun), or an arbitrary week
     * when ?week=YYYY-MM-DD (any date in that week) is given.
     */
    public function weekly(Request $request): JsonResponse
    {
        $request->validate(['week' => ['sometimes', 'date']]);

        $anchor = $request->date('week') ? Carbon::parse($request->date('week')) : now();
        $start = $anchor->clone()->startOfWeek();
        $end = $anchor->clone()->endOfWeek();

        $activities = Activity::query()
            ->whereBetween('started_at', [$start, $end])
            ->get();

        return response()->json([
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'activity_count' => $activities->count(),
            'distance_km' => round($activities->sum('distance_m') / 1000, 2),
            'moving_time_h' => round($activities->sum('moving_time_s') / 3600, 2),
            'elevation_gain_m' => round($activities->sum('total_elevation_gain_m'), 0),
            'average_heartrate' => $this->weightedAverage($activities, 'average_heartrate', 'moving_time_s'),
            'by_type' => $activities->groupBy('type')->map->count(),
        ]);
    }

    /**
     * Weekly distance/time series over the last N weeks (default 8), for
     * spotting training-load trends.
     */
    public function trends(Request $request): JsonResponse
    {
        $request->validate(['weeks' => ['sometimes', 'integer', 'min:1', 'max:52']]);

        $weeks = $request->integer('weeks', 8);
        $series = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = now()->subWeeks($i)->endOfWeek();

            $activities = Activity::query()->whereBetween('started_at', [$start, $end])->get();

            $series[] = [
                'week_start' => $start->toDateString(),
                'distance_km' => round($activities->sum('distance_m') / 1000, 2),
                'moving_time_h' => round($activities->sum('moving_time_s') / 3600, 2),
                'activity_count' => $activities->count(),
            ];
        }

        return response()->json(['weeks' => $series]);
    }

    private function weightedAverage($activities, string $valueField, string $weightField): ?float
    {
        $withValue = $activities->filter(fn ($a) => $a->{$valueField} !== null);

        $totalWeight = $withValue->sum($weightField);

        if ($totalWeight <= 0) {
            return null;
        }

        $weightedSum = $withValue->sum(fn ($a) => $a->{$valueField} * $a->{$weightField});

        return round($weightedSum / $totalWeight, 1);
    }
}
