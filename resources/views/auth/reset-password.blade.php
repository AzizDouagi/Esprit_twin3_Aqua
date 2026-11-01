@extends('layouts.auth')
@section('title', 'Nouveau mot de passe')
@section('heading', 'Nouveau mot de passe')
@section('lead', 'Choisissez un nouveau mot de passe pour votre compte.')
@section('content')
    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control" required autocomplete="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" class="form-control" required autofocus autocomplete="new-password">
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmation</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
        </div>
        <button class="btn btn-aqua w-100" type="submit">Réinitialiser le mot de passe</button>
    </form>
@endsection
