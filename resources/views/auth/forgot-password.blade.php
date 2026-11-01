@extends('layouts.auth')
@section('title', 'Mot de passe oublié')
@section('heading', 'Mot de passe oublié')
@section('lead', 'Saisissez votre email : nous vous enverrons un lien de réinitialisation (valable '.config('auth.passwords.users.expire').' minutes).')
@section('content')
    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
        </div>
        <button class="btn btn-aqua w-100" type="submit"><i class="bi bi-envelope me-1"></i>Envoyer le lien</button>
    </form>
    <p class="text-center small mt-4 mb-0"><a href="{{ route('login') }}">Retour à la connexion</a></p>
@endsection
