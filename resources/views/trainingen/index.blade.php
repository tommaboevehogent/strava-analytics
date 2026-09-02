@extends('layouts.app')

@section('title', 'Trainingen')

@section('content')
    <style>
        main { max-width: 900px !important; }
        .pill {
            display: inline-block;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--muted);
        }
        .pill:hover { border-color: var(--accent); color: var(--accent); }
        .pill.active { background: var(--accent); border-color: var(--accent); color: #fff; }
    </style>

    <nav class="topnav">
        <a href="{{ route('tokens.index') }}">Mijn API-tokens</a>
        <span>Ingelogd als {{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn-link">Uitloggen</button>
        </form>
    </nav>

    <span class="eyebrow">Trainingen</span>
    <h1>Jouw activiteiten</h1>
    <p class="lead">
        Rechtstreeks uit dezelfde data als <code>/api/activities</code> — deze pagina is gewoon
        een leesbare weergave erbovenop, geen aparte databron.
    </p>

    <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:1.5rem;">
        <a href="{{ route('trainingen.index') }}" class="pill @if(!$activeType) active @endif">Alle</a>
        @foreach ($types as $type)
            <a href="{{ route('trainingen.index', ['type' => $type]) }}"
               class="pill @if($activeType === $type) active @endif">
                {{ $type }}
            </a>
        @endforeach
    </div>

    @if ($activities->isEmpty())
        <p class="lead">
            Nog geen trainingen gevonden
            @if ($activeType)
                voor type "{{ $activeType }}"
            @endif
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Naam</th>
                    <th>Type</th>
                    <th>Afstand</th>
                    <th>Tempo</th>
                    <th>Hartslag</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activities as $activity)
                    <tr>
                        <td><a href="{{ route('trainingen.show', $activity) }}">{{ $activity->started_at->format('d/m/Y') }}</a></td>
                        <td><a href="{{ route('trainingen.show', $activity) }}">{{ $activity->name }}</a></td>
                        <td>{{ $activity->type }}</td>
                        <td>{{ $activity->distanceKm() }} km</td>
                        <td>
                            @if ($activity->paceSecPerKm())
                                {{ sprintf('%d:%02d', intdiv($activity->paceSecPerKm(), 60), $activity->paceSecPerKm() % 60) }} /km
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $activity->average_heartrate ? round($activity->average_heartrate).' bpm' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            @if ($activities->previousPageUrl())
                <a href="{{ $activities->previousPageUrl() }}" class="btn-link">← Vorige</a>
            @else
                <span></span>
            @endif

            <span style="font-size:0.8rem; color:var(--muted);">
                Pagina {{ $activities->currentPage() }} van {{ $activities->lastPage() }}
                ({{ $activities->total() }} in totaal)
            </span>

            @if ($activities->nextPageUrl())
                <a href="{{ $activities->nextPageUrl() }}" class="btn-link">Volgende →</a>
            @else
                <span></span>
            @endif
        </div>
    @endif

    <footer>
        <a href="{{ url('/') }}">← Terug naar overzicht</a>
    </footer>
@endsection
