<?php

namespace App\Jobs;

use App\Services\ActivitySyncer;
use App\Services\StravaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public int $athleteId,
        public int $stravaActivityId,
    ) {}

    public function handle(StravaClient $strava, ActivitySyncer $syncer): void
    {
        $payload = $strava->getActivity($this->athleteId, $this->stravaActivityId);

        $syncer->upsert($this->athleteId, $payload);
    }
}
