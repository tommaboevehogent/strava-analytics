# Strava Analytics API

Persoonlijke Laravel-app die je Strava-trainingsdata via de officiële API
binnenhaalt (OAuth + webhook), opslaat in een relationele database, en
ontsluit via een eigen REST API met wekelijkse/trend-statistieken.

Bedoeld als een compact, doorleefd PHP/Laravel-portfolioproject naast je
bestaande Azure/Databricks-pipeline — zelfde brondata (Strava), ander deel
van de stack: API-ontwerp, OAuth, webhooks, queues, auth, tests.

## Architectuur in het kort

```
Strava OAuth  ──▶  strava_tokens (access/refresh token, incl. auto-refresh)
Strava Webhook ──▶  SyncActivityJob (queue)  ──▶  activities tabel
php artisan strava:sync  ──▶  historische backfill, zelfde upsert-pad
                                        │
                                        ▼
                         REST API (Sanctum) — /api/activities, /api/stats/*
```

- `app/Services/StravaClient.php` — OAuth exchange/refresh, rate-limit-aware HTTP-calls
- `app/Services/ActivitySyncer.php` — mapt een Strava-payload naar de `activities`-tabel
- `app/Http/Controllers/StravaAuthController.php` — eenmalige OAuth-handshake
- `app/Http/Controllers/StravaWebhookController.php` — subscription-validatie + push events
- `app/Jobs/SyncActivityJob.php` — queued job die één activiteit ophaalt en opslaat
- `app/Console/Commands/SyncStravaActivities.php` — `artisan strava:sync` voor backfill
- `app/Http/Controllers/Api/{ActivityController,StatsController}.php` — de REST API

## Setup

Dit project is hier opgezet zonder `composer install` te draaien (het
sandbox-netwerk waarin ik dit gebouwd heb blokkeert Packagist), dus dat is
letterlijk je eerste commando lokaal:

```bash
composer install

cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # of zet DB_CONNECTION op mysql/pgsql in .env
php artisan migrate
```

### Strava-app registreren

1. Ga naar https://www.strava.com/settings/api en maak een app aan.
   - Authorization Callback Domain: `localhost` (lokaal) of je echte domein.
2. Zet in `.env`:
   ```
   STRAVA_CLIENT_ID=...
   STRAVA_CLIENT_SECRET=...
   STRAVA_REDIRECT_URI=http://localhost:8000/strava/callback
   STRAVA_WEBHOOK_VERIFY_TOKEN=kies-zelf-een-random-string
   ```

### Account koppelen

```bash
php artisan serve
```

Bezoek `http://localhost:8000/strava/connect`, keur de app goed op Strava —
je access/refresh token worden opgeslagen in `strava_tokens`. Dit hoef je
maar één keer te doen; alles daarna ververst de token automatisch.

### Historische data ophalen

```bash
php artisan strava:sync
# of vanaf een datum:
php artisan strava:sync --after=2026-01-01
```

### Live bijhouden via webhook

Strava's webhook-endpoint moet publiek bereikbaar zijn (dus niet
`localhost` — gebruik lokaal bijvoorbeeld `ngrok http 8000` tijdens
ontwikkeling). Abonneer je met de [Strava webhook create-subscription
call](https://developers.strava.com/docs/webhooks/#create-a-subscription)
naar `https://jouw-domein/webhooks/strava`, met dezelfde
`STRAVA_WEBHOOK_VERIFY_TOKEN`. Draai een queue worker zodat events ook echt
verwerkt worden:

```bash
php artisan queue:work
```

### Een API-token voor jezelf aanmaken

De `/api/*`-routes zijn Sanctum-beveiligd. Voor persoonlijk gebruik volstaat
een simpele Tinker-sessie:

```bash
php artisan tinker
>>> $user = \App\Models\User::factory()->create();
>>> $user->createToken('personal')->plainTextToken
```

Gebruik die token als `Authorization: Bearer <token>` header.

### Tests

```bash
php artisan test
```

`StravaClientTest` gebruikt `Http::fake()` — er gaat geen echt netwerkverkeer
naartoe Strava tijdens tests.

## API

| Methode | Route | Omschrijving |
|---|---|---|
| GET | `/api/activities` | Lijst, filters: `type`, `from`, `to`, `per_page` |
| GET | `/api/activities/{id}` | Eén activiteit |
| GET | `/api/stats/weekly` | Totalen voor de huidige (of `?week=YYYY-MM-DD`) week |
| GET | `/api/stats/trends` | Wekelijkse tijdreeks, `?weeks=8` (default) |

## Weekplan

Realistisch tempo voor naast je andere dingen — elke dag ca. 1-2 uur.

- **Dag 1 — Setup & OAuth**: `composer install`, Strava-app registreren,
  `/strava/connect` → `/strava/callback` end-to-end laten werken, token
  in de database zien staan.
- **Dag 2 — Backfill**: `strava:sync` draaien op je eigen historiek,
  edge cases opvangen (pagination, ontbrekende velden in oudere activiteiten).
- **Dag 3 — Webhook**: `ngrok` erbij, subscription aanmaken bij Strava,
  een test-activiteit loggen en zien binnenkomen via de queue.
- **Dag 4 — REST API**: Sanctum-tokens, `/api/activities` en filters
  uittesten met een `.http`-bestand of Postman/Insomnia.
- **Dag 5 — Stats-endpoints**: `/api/stats/weekly` en `/api/stats/trends`
  afwerken, `php artisan test` groen krijgen.
- **Dag 6 — Polish**: rate-limit gedrag testen (Strava's 429), logging,
  eventueel een klein Blade/Livewire-dashboardje met Chart.js bovenop de
  stats-endpoints.
- **Dag 7 — Deploy**: bv. Laravel Cloud, Forge, of gewoon een goedkope VPS
  met Nginx + PHP-FPM + supervisor voor de queue worker.

## Mogelijke vervolgstappen (na deze week)

- GPX/polyline-visualisatie op een kaart
- Trainingsload-metrics (acute:chronic workload ratio) bovenop `activities`
- Aparte read-model/export naar dezelfde Power BI-dashboard als je
  Azure-pipeline, als vergelijkingsmateriaal tussen beide stacks
