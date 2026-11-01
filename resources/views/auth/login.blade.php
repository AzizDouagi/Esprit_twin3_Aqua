@extends('layouts.auth')
@section('title', 'Connexion')
@section('heading', 'Connexion')
@section('lead', 'Accédez à votre espace AquaSecure.')
@section('content')
    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label">Mot de passe</label>
                <a href="{{ route('password.request') }}" class="small">Mot de passe oublié ?</a>
            </div>
            <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>
        <button class="btn btn-aqua w-100" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i>Se connecter</button>
    </form>
    <p class="text-center small mt-4 mb-0">Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a></p>
@endsection
