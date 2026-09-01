<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{activity}', [ActivityController::class, 'show']);

    Route::get('/stats/weekly', [StatsController::class, 'weekly']);
    Route::get('/stats/trends', [StatsController::class, 'trends']);

    Route::get('/coach/weekly-insight', [CoachController::class, 'weeklyInsight']);
});
