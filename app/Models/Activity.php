<?php

namespace App\Models;

use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'strava_id',
        'athlete_id',
        'name',
        'type',
        'sport_type',
        'distance_m',
        'moving_time_s',
        'elapsed_time_s',
        'total_elevation_gain_m',
        'started_at',
        'average_speed_ms',
        'max_speed_ms',
        'average_heartrate',
        'max_heartrate',
        'average_cadence',
        'kudos_count',
        'map_polyline',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'raw_payload' => 'array',
            'distance_m' => 'float',
            'total_elevation_gain_m' => 'float',
            'average_speed_ms' => 'float',
            'max_speed_ms' => 'float',
            'average_heartrate' => 'float',
            'max_heartrate' => 'float',
            'average_cadence' => 'float',
        ];
    }

    public function distanceKm(): float
    {
        return round($this->distance_m / 1000, 2);
    }

    /**
     * Average pace in seconds per km. Null when there's no meaningful distance.
     */
    public function paceSecPerKm(): ?float
    {
        if ($this->distance_m <= 0) {
            return null;
        }

        return round($this->moving_time_s / ($this->distance_m / 1000), 1);
    }
}
