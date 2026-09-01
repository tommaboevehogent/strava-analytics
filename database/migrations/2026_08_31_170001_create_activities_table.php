<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('strava_id')->unique();
            $table->unsignedBigInteger('athlete_id')->index();
            $table->string('name');
            $table->string('type')->index(); // Run, TrailRun, Ride, Hike, ...
            $table->string('sport_type')->nullable();
            $table->decimal('distance_m', 10, 2)->default(0);
            $table->unsignedInteger('moving_time_s')->default(0);
            $table->unsignedInteger('elapsed_time_s')->default(0);
            $table->decimal('total_elevation_gain_m', 8, 2)->default(0);
            $table->timestamp('started_at')->index();
            $table->decimal('average_speed_ms', 8, 3)->nullable();
            $table->decimal('max_speed_ms', 8, 3)->nullable();
            $table->decimal('average_heartrate', 6, 2)->nullable();
            $table->decimal('max_heartrate', 6, 2)->nullable();
            $table->decimal('average_cadence', 6, 2)->nullable();
            $table->unsignedInteger('kudos_count')->default(0);
            $table->string('map_polyline', 8192)->nullable();
            $table->json('raw_payload')->nullable(); // full Strava response, for anything not modeled above
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
