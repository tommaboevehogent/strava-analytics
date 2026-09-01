@extends('layouts.app')

@section('title', 'API-tokens — ' . config('app.name'))

@section('content')
    <nav class="topnav">
        <span>Ingelogd als {{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn-link">Uitloggen</button>
        </form>
    </nav>

    <span class="eyebrow">API-tokens</span>
    <h1>Jouw Sanctum-tokens</h1>
    <p class="lead">Gebruik een token als <code>Authorization: Bearer &lt;token&gt;</code> header bij <code>/api/*</code>-aanvragen.</p>

    @if (session('plain_text_token'))
        <div class="alert alert-success">
            <strong>Nieuw token aangemaakt — bewaar het nu, het wordt hierna niet meer getoond:</strong><br>
            <code>{{ session('plain_text_token') }}</code>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <table>
        <thead>
            <tr><th>Naam</th><th>Aangemaakt</th><th>Laatst gebruikt</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($tokens as $token)
                <tr>
                    <td>{{ $token->name }}</td>
                    <td>{{ $token->created_at->diffForHumans() }}</td>
                    <td>{{ $token->last_used_at?->diffForHumans() ?? 'nooit' }}</td>
                    <td>
                        <form method="POST" action="{{ route('tokens.destroy', $token->id) }}"
                              onsubmit="return confirm('Dit token intrekken? Elke app die het gebruikt, verliest meteen toegang.')"
                              style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-link">intrekken</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="color:var(--muted);">Nog geen tokens aangemaakt.</td></tr>
            @endforelse
        </tbody>
    </table>

    <form method="POST" action="{{ route('tokens.store') }}" class="inline-form">
        @csrf
        <input type="text" name="name" placeholder="naam, bv. 'persoonlijk gebruik'" required>
        <button type="submit" class="btn">Nieuw token</button>
    </form>
@endsection
