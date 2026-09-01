<?php

use App\Http\Controllers\StravaAuthController;
use App\Http\Controllers\StravaWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// nette respons bij elk niet geauth verzoek
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

// --- Strava OAuth (one-time authorization, run this yourself as the app owner) ---
Route::get('/strava/connect', [StravaAuthController::class, 'redirect'])->name('strava.connect');
Route::get('/strava/callback', [StravaAuthController::class, 'callback'])->name('strava.callback');

// --- Strava webhook (subscription validation + push events) ---
// Strava calls this URL directly, it is intentionally outside auth/CSRF.
Route::get('/webhooks/strava', [StravaWebhookController::class, 'verify']);
Route::post('/webhooks/strava', [StravaWebhookController::class, 'handle']);
