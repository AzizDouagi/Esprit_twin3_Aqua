@extends('layouts.front')
@section('title', 'Mon espace')
@section('content')
<div class="container py-5">
    <div class="d-flex align-items-center gap-3 mb-4">
        @include('partials.avatar', ['user' => $user, 'size' => 64])
        <div>
            <h1 class="h3 mb-0">Bonjour {{ $user->name }}</h1>
            <span class="badge text-bg-info">{{ $user->roleLabel() }}</span>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-4"><div class="info-tile">
            <h2 class="h6"><i class="bi bi-buildings me-1"></i>Infrastructures</h2>
            <p class="small text-secondary">Consultez l’état du réseau.</p>
            <a href="{{ route('front.infrastructures.index') }}" class="btn btn-sm btn-aqua">Voir</a>
        </div></div>
        <div class="col-md-4"><div class="info-tile">
            <h2 class="h6"><i class="bi bi-person-gear me-1"></i>Mon profil</h2>
            <p class="small text-secondary">Informations, photo, mot de passe.</p>
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-aqua">Modifier</a>
        </div></div>
        <div class="col-md-4"><div class="info-tile">
            <h2 class="h6"><i class="bi bi-shield-lock me-1"></i>Sécurité</h2>
            <p class="small text-secondary">Double authentification (2FA).</p>
            <a href="{{ route('profile.security') }}" class="btn btn-sm btn-aqua">Configurer</a>
        </div></div>
    </div>
</div>
@endsection
