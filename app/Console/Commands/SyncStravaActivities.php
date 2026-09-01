<?php

namespace App\Console\Commands;

use App\Models\StravaToken;
use App\Services\ActivitySyncer;
use App\Services\StravaClient;
use Illuminate\Console\Command;

/**
 * Historical backfill / catch-up sync.
 *
 * The webhook keeps things current once it's set up, but this command is
 * what you run first to pull your existing Strava history, and it's a
 * useful safety net to re-run periodically in case a webhook event is
 * ever missed.
 *
 *   php artisan strava:sync                 # full history
 *   php artisan strava:sync --after=2026-01-01
 */
class SyncStravaActivities extends Command
{
    protected $signature = 'strava:sync {--after= : Only sync activities after this date (Y-m-d)}';

    protected $description = 'Backfill activities from Strava for the connected athlete.';

    public function handle(StravaClient $strava, ActivitySyncer $syncer): int
    {
        $token = StravaToken::first();

        if (! $token) {
            $this->error('No Strava account connected yet. Visit /strava/connect first.');

            return self::FAILURE;
        }

        $after = $this->option('after')
            ? \Illuminate\Support\Carbon::parse($this->option('after'))->timestamp
            : null;

        $page = 1;
        $total = 0;

        do {
            $this->info("Fetching page {$page}...");

            $activities = $strava->listActivities($token->athlete_id, $after, $page);

            foreach ($activities as $activity) {
                $syncer->upsert($token->athlete_id, $activity);
                $total++;
            }

            $page++;
        } while (count($activities) === 100); // Strava's max per_page

        $this->info("Done. Synced {$total} activities.");

        return self::SUCCESS;
    }
}
