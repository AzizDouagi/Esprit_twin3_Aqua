@extends('layouts.auth')
@section('title', 'Confirmation du mot de passe')
@section('heading', 'Zone sécurisée')
@section('lead', 'Confirmez votre mot de passe pour continuer.')
@section('content')
    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" type="password" name="password" class="form-control" required autofocus autocomplete="current-password">
        </div>
        <button class="btn btn-aqua w-100" type="submit">Confirmer</button>
    </form>
@endsection
