@extends('layouts.auth')
@section('title', 'Double authentification')
@section('heading', 'Double authentification')
@section('lead', 'Saisissez le code de votre application d’authentification, ou un code de récupération.')
@section('content')
    <form method="POST" action="{{ route('two-factor.login') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">Code à 6 chiffres</label>
            <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" class="form-control" autofocus>
        </div>
        <div class="text-center text-secondary small mb-3">— ou —</div>
        <div class="mb-3">
            <label for="recovery_code" class="form-label">Code de récupération</label>
            <input id="recovery_code" type="text" name="recovery_code" autocomplete="off" class="form-control">
        </div>
        <button class="btn btn-aqua w-100" type="submit"><i class="bi bi-shield-lock me-1"></i>Valider</button>
    </form>
    <p class="text-center small mt-4 mb-0"><a href="{{ route('login') }}">Retour à la connexion</a></p>
@endsection
