<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StravaAuthController;
use App\Http\Controllers\StravaWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- Sessie-login voor de projecteigenaar (los van de Sanctum API-tokens) ---
// Laravel's default auth-middleware valt terug op deze named route "login"
// zodra een niet-ingelogd verzoek geen JSON verwacht. Voor /api/* blijft de
// respons altijd JSON (zie shouldRenderJsonWhen in bootstrap/app.php), dus
// dit heeft geen invloed op de API zelf — enkel op deze webpagina's.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/tokens', [ApiTokenController::class, 'index'])->name('tokens.index');
    Route::post('/tokens', [ApiTokenController::class, 'store'])->name('tokens.store');
    Route::delete('/tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('tokens.destroy');
});

// --- Strava OAuth (one-time authorization, run this yourself as the app owner) ---
Route::get('/strava/connect', [StravaAuthController::class, 'redirect'])->name('strava.connect');
Route::get('/strava/callback', [StravaAuthController::class, 'callback'])->name('strava.callback');

// --- Strava webhook (subscription validation + push events) ---
// Strava calls this URL directly, it is intentionally outside auth/CSRF.
Route::get('/webhooks/strava', [StravaWebhookController::class, 'verify']);
Route::post('/webhooks/strava', [StravaWebhookController::class, 'handle']);
