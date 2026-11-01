@extends('layouts.auth')
@section('title', 'Inscription')
@section('heading', 'Créer un compte')
@section('lead', 'Déclarez des incidents et suivez vos signalements.')
@section('content')
    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nom complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus autocomplete="name">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">
            <div class="form-text">8 caractères minimum.</div>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmation du mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
        </div>
        <button class="btn btn-aqua w-100" type="submit"><i class="bi bi-person-plus me-1"></i>S’inscrire</button>
    </form>
    <p class="text-center small mt-4 mb-0">Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a></p>
@endsection
