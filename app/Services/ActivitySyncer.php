<?php

namespace App\Services;

use App\Models\Activity;

/**
 * Maps a raw Strava activity payload onto our activities table.
 * Kept separate from StravaClient so the HTTP layer and the persistence
 * mapping can be tested independently.
 */
class ActivitySyncer
{
    public function upsert(int $athleteId, array $payload): Activity
    {
        return Activity::updateOrCreate(
            ['strava_id' => $payload['id']],
            [
                'athlete_id' => $athleteId,
                'name' => $payload['name'] ?? 'Untitled activity',
                'type' => $payload['type'] ?? 'Workout',
                'sport_type' => $payload['sport_type'] ?? null,
                'distance_m' => $payload['distance'] ?? 0,
                'moving_time_s' => $payload['moving_time'] ?? 0,
                'elapsed_time_s' => $payload['elapsed_time'] ?? 0,
                'total_elevation_gain_m' => $payload['total_elevation_gain'] ?? 0,
                'started_at' => $payload['start_date'] ?? now(),
                'average_speed_ms' => $payload['average_speed'] ?? null,
                'max_speed_ms' => $payload['max_speed'] ?? null,
                'average_heartrate' => $payload['average_heartrate'] ?? null,
                'max_heartrate' => $payload['max_heartrate'] ?? null,
                'average_cadence' => $payload['average_cadence'] ?? null,
                'kudos_count' => $payload['kudos_count'] ?? 0,
                'map_polyline' => $payload['map']['summary_polyline'] ?? null,
                'raw_payload' => $payload,
            ]
        );
    }
}
