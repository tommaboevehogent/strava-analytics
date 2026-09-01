@extends('layouts.app')

@section('title', 'Inloggen — ' . config('app.name'))

@section('content')
    <span class="eyebrow">Login</span>
    <h1>Inloggen</h1>
    <p class="lead">Alleen voor jezelf als eigenaar van dit project — hier kan je een API-token voor jezelf aanmaken.</p>

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" class="stacked">
        @csrf

        <label>
            E-mailadres
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>

        <label>
            Wachtwoord
            <input type="password" name="password" required>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="remember"> Onthoud mij
        </label>

        <button type="submit" class="btn">Inloggen</button>
    </form>
@endsection
