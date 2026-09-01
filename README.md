# Strava Analytics API

Een Laravel REST API die mijn eigen Strava-trainingsdata ontsluit: OAuth2-koppeling
met Strava, real-time synchronisatie via webhooks, achtergrondverwerking via queues,
en een token-geauthenticeerde REST API met wekelijkse en trend-statistieken.

**Live:** `https://strava-analytics-production-i0zvv7.laravel.cloud` (Laravel Cloud)

# Werking

Je bezoekt /strava/connect in de browser. 

routes/web.php stuurt dat door naar StravaAuthController::redirect() (app/Http/Controllers/StravaAuthController.php). 

Die methode bouwt de Strava-autorisatie-URL op via StravaClient::buildAuthorizeUrl() (app/Services/StravaClient.php)

Strava stuurt je terug naar /strava/callback, dat naar StravaAuthController::callback() gaat. 

Die methode roept StravaClient::exchangeCodeForToken() aan, die een POST-request naar Strava's token-endpoint doet en het antwoord (access token, refresh token, verlooptijd) opslaat via StravaToken::updateOrCreate() — dat schrijft naar de strava_tokens-tabel, waarvan de structuur vastligt in database/migrations/2026_08_31_170000_create_strava_tokens_table.php en het model zelf in app/Models/StravaToken.php staat.

Ververst het automatisch als het verlopen is (StravaToken::isExpired(), gecheckt in StravaClient::accessTokenFor()).

Strava zelf een POST naar /webhooks/strava bij nieuwe activiteit. Dat komt binnen bij StravaWebhookController::handle() (app/Http/Controllers/StravaWebhookController.php). 

Die methode kijkt alleen naar object_type=activity en aspect_type=create/update, en stuurt de rest van het werk door naar de achtergrond via SyncActivityJob::dispatch() (app/Jobs/SyncActivityJob.php).

De job zelf draait apart, opgepikt door php artisan queue:work (op productie: het achtergrondproces op Laravel Cloud). 

SyncActivityJob::handle() haalt de volledige activiteit op via StravaClient::getActivity(), en geeft die door aan ActivitySyncer::upsert() (app/Services/ActivitySyncer.php), die de ruwe Strava-data mapt naar de kolommen van de activities-tabel (structuur in database/migrations/2026_08_31_170001_create_activities_table.php, model in app/Models/Activity.php) en wegschrijft.

Jij draait zelf php artisan strava:sync in de terminal. Dat commando zit in app/Console/Commands/SyncStravaActivities.php. 

Het haalt het opgeslagen token op, roept StravaClient::listActivities() pagina per pagina aan (Strava geeft er max 100 per keer terug), en geeft elke activiteit door aan diezelfde ActivitySyncer::upsert() als hierboven. 

Dit is de enige stroom die iemand anders (of een tool zoals Postman) rechtstreeks aanspreekt, met een Sanctum-token in de Authorization-header. routes/api.php stuurt /api/activities en /api/activities/{id} naar ActivityController (app/Http/Controllers/Api/ActivityController.php), en /api/stats/weekly / /api/stats/trends naar StatsController (app/Http/Controllers/Api/StatsController.php). 

Beide lezen gewoon uit de activities-tabel via het Activity-model — geen contact met Strava zelf, puur een read op je eigen database. Wat er teruggaat als JSON wordt bepaald door ActivityResource (app/Http/Resources/ActivityResource.php), die kiest welke velden getoond worden en in welke vorm (bv. distance_km in plaats van de ruwe distance_m).

