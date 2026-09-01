<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CoachClient;
use App\Services\TrainingFeatures;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CoachController extends Controller
{
    public function weeklyInsight(TrainingFeatures $features, CoachClient $coach): JsonResponse
    {
        // ISO year+week as the cache key ("oW") — one generated insight per
        // calendar week, regenerated automatically once the week rolls over.
        $cacheKey = 'coach:weekly-insight:'.now()->format('oW');

        $payload = Cache::remember($cacheKey, now()->addDay(), function () use ($features, $coach) {
            $snapshot = $features->weeklySnapshot();

            return [
                'features' => $snapshot,
                'insight' => $coach->weeklyInsight($snapshot),
                'generated_at' => now()->toIso8601String(),
            ];
        });

        return response()->json($payload);
    }
}
