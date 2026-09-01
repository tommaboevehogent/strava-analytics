<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'strava_id' => $this->strava_id,
            'name' => $this->name,
            'type' => $this->type,
            'sport_type' => $this->sport_type,
            'distance_km' => $this->distanceKm(),
            'moving_time_s' => $this->moving_time_s,
            'elapsed_time_s' => $this->elapsed_time_s,
            'elevation_gain_m' => $this->total_elevation_gain_m,
            'pace_sec_per_km' => $this->paceSecPerKm(),
            'average_heartrate' => $this->average_heartrate,
            'max_heartrate' => $this->max_heartrate,
            'kudos_count' => $this->kudos_count,
            'started_at' => $this->started_at?->toIso8601String(),
        ];
    }
}
