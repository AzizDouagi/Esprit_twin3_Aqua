@extends('layouts.front')

@section('title', 'Accueil')

@section('content')
    <header class="hero py-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">AquaSecure</h1>
                    <p class="lead mb-4">Suivi local des infrastructures d’eau potable et des incidents opérationnels.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('front.infrastructures.index') }}" class="btn btn-light"><i class="bi bi-buildings me-1"></i> Explorer les infrastructures</a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light"><i class="bi bi-speedometer2 me-1"></i> BackOffice</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="soft-band py-4">
        <div class="container">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-tile">
                        <div class="text-secondary small">Infrastructures surveillées</div>
                        <div class="fs-2 fw-bold">{{ $totalInfrastructures }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-tile">
                        <div class="text-secondary small">Incidents ouverts</div>
                        <div class="fs-2 fw-bold text-danger">{{ $totalIncidentsOuverts }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">Incidents récents</h2>
                <span class="text-secondary small">Consultation publique</span>
            </div>
            <div class="row g-3">
                @forelse ($incidentsRecents as $incident)
                    <div class="col-md-6">
                        <article class="info-tile">
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <h3 class="h6 mb-0">{{ $incident->titre }}</h3>
                                <span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span>
                            </div>
                            <p class="text-secondary small mb-2">{{ $incident->infrastructure?->nom }} - {{ $incident->date_incident->format('d/m/Y') }}</p>
                            <p class="mb-0">{{ str($incident->description)->limit(120) }}</p>
                        </article>
                    </div>
                @empty
                    <div class="col-12"><p class="text-secondary mb-0">Aucun incident récent à afficher.</p></div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
