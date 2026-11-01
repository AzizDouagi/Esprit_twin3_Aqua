@extends('layouts.auth')
@section('title', 'Vérification de l’email')
@section('heading', 'Vérifiez votre adresse email')
@section('lead', 'Un lien de confirmation a été envoyé à '.(auth()->user()->email ?? 'votre adresse').'. Cliquez dessus pour activer votre compte.')
@section('content')
    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">Un nouveau lien de vérification vient d’être envoyé.</div>
    @endif
    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button class="btn btn-aqua w-100" type="submit"><i class="bi bi-envelope-arrow-up me-1"></i>Renvoyer le lien</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-secondary w-100" type="submit">Se déconnecter</button>
    </form>
@endsection
