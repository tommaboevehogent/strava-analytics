<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        $distanceM = $this->faker->randomFloat(2, 3000, 25000);
        $movingTimeS = (int) ($distanceM / $this->faker->randomFloat(2, 2.5, 3.5)); // ~ 5-7 min/km pace range

        return [
            'strava_id' => $this->faker->unique()->numberBetween(1_000_000_000, 9_999_999_999),
            'athlete_id' => 1,
            'name' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['Run', 'TrailRun', 'Hike']),
            'sport_type' => 'Run',
            'distance_m' => $distanceM,
            'moving_time_s' => $movingTimeS,
            'elapsed_time_s' => $movingTimeS + $this->faker->numberBetween(0, 300),
            'total_elevation_gain_m' => $this->faker->randomFloat(2, 0, 400),
            'started_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'average_speed_ms' => $distanceM / max($movingTimeS, 1),
            'max_speed_ms' => $this->faker->randomFloat(3, 3, 6),
            'average_heartrate' => $this->faker->randomFloat(1, 130, 175),
            'max_heartrate' => $this->faker->randomFloat(1, 175, 195),
            'average_cadence' => $this->faker->randomFloat(1, 75, 90),
            'kudos_count' => $this->faker->numberBetween(0, 20),
            'map_polyline' => null,
            'raw_payload' => [],
        ];
    }
}
