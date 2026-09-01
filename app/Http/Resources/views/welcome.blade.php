@extends('layouts.app')

@section('content')
    <nav class="topnav">
        @auth
            <a href="{{ route('tokens.index') }}">Mijn API-tokens</a>
        @else
            <a href="{{ route('login') }}">Log in</a>
        @endauth
    </nav>

    <span class="eyebrow">Personal project</span>
    <h1>Strava Analytics API</h1>
    <p class="lead">
        Een Laravel REST API die Strava-trainingsdata ontsluit via OAuth,
        real-time webhooks en een token-geauthenticeerde API met
        wekelijkse/trend-statistieken.
    </p>

    <div class="status">
        <span class="dot"></span> API online — geen publieke UI, alleen endpoints
    </div>

    <table>
        <thead>
            <tr><th>Methode</th><th>Route</th></tr>
        </thead>
        <tbody>
            <tr><td>GET</td><td><code>/api/activities</code></td></tr>
            <tr><td>GET</td><td><code>/api/activities/{id}</code></td></tr>
            <tr><td>GET</td><td><code>/api/stats/weekly</code></td></tr>
            <tr><td>GET</td><td><code>/api/stats/trends</code></td></tr>
        </tbody>
    </table>

    <footer>
        Alle <code>/api/*</code>-routes vereisen een Sanctum-token.
        Broncode en volledige documentatie:
        <a href="https://github.com/tommaboevehogent/strava-analytics" target="_blank" rel="noopener">GitHub</a>.
    </footer>
@endsection
