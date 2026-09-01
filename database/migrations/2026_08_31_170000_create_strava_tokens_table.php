<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-row-per-athlete token store. For a personal project this
     * table typically holds exactly one row (your own Strava account),
     * but it's keyed by athlete_id so it scales to more if needed.
     */
    public function up(): void
    {
        Schema::create('strava_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('athlete_id')->unique();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at');
            $table->json('scope')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strava_tokens');
    }
};
