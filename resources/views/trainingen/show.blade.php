@extends('layouts.app')

@section('title', $activity->name)

@section('content')
    <nav class="topnav">
        <a href="{{ route('trainingen.index') }}">← Trainingen</a>
        <a href="{{ route('tokens.index') }}">Mijn API-tokens</a>
    </nav>

    <span class="eyebrow">{{ $activity->type }}</span>
    <h1>{{ $activity->name }}</h1>
    <p class="lead">{{ $activity->started_at->translatedFormat('l d F Y, H:i') }}</p>

    <table>
        <tbody>
            <tr><th>Afstand</th><td>{{ $activity->distanceKm() }} km</td></tr>
            <tr><th>Bewegingstijd</th><td>{{ gmdate('H:i:s', $activity->moving_time_s) }}</td></tr>
            <tr><th>Totale tijd</th><td>{{ gmdate('H:i:s', $activity->elapsed_time_s) }}</td></tr>
            <tr>
                <th>Tempo</th>
                <td>
                    @if ($activity->paceSecPerKm())
                        {{ sprintf('%d:%02d', intdiv($activity->paceSecPerKm(), 60), $activity->paceSecPerKm() % 60) }} /km
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr><th>Hoogtemeters</th><td>{{ $activity->total_elevation_gain_m ? round($activity->total_elevation_gain_m).' m' : '—' }}</td></tr>
            <tr><th>Gem. hartslag</th><td>{{ $activity->average_heartrate ? round($activity->average_heartrate).' bpm' : '—' }}</td></tr>
            <tr><th>Max. hartslag</th><td>{{ $activity->max_heartrate ? round($activity->max_heartrate).' bpm' : '—' }}</td></tr>
            <tr><th>Gem. cadans</th><td>{{ $activity->average_cadence ? round($activity->average_cadence).' spm' : '—' }}</td></tr>
            <tr><th>Kudos</th><td>{{ $activity->kudos_count }}</td></tr>
        </tbody>
    </table>

    @if ($activity->raw_payload)
        <details style="margin-bottom:1.5rem;">
            <summary style="cursor:pointer; color:var(--muted); font-size:0.85rem;">Ruwe Strava-payload tonen</summary>
            <pre style="overflow-x:auto; font-size:0.75rem; background:var(--bg); border:1px solid var(--border); border-radius:0.4rem; padding:0.75rem; margin-top:0.75rem;">{{ json_encode($activity->raw_payload, JSON_PRETTY_PRINT) }}</pre>
        </details>
    @endif

    <footer>
        <a href="{{ route('trainingen.index') }}">← Terug naar het overzicht</a>
    </footer>
@endsection
