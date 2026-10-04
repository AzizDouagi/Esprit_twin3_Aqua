@extends('layouts.front')

@section('title', $infrastructure->nom)

@section('content')
    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <h1 class="h2 mb-2">{{ $infrastructure->nom }}</h1>
                    <p class="text-secondary mb-0">{{ $infrastructure->type }} - {{ $infrastructure->localisation }}</p>
                </div>
                <div><span class="badge text-bg-{{ $infrastructure->statut_badge }}">{{ $infrastructure->etat }}</span></div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="info-tile">
                        <h2 class="h5">Informations</h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Date installation</dt>
                            <dd class="col-sm-7">{{ $infrastructure->date_installation?->format('d/m/Y') ?? 'Non renseignée' }}</dd>
                            <dt class="col-sm-5">Incidents</dt>
                            <dd class="col-sm-7">{{ $infrastructure->incidents_count }}</dd>
                        </dl>
                        <p class="mb-0 mt-3">{{ $infrastructure->description ?: 'Aucune description.' }}</p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="info-tile">
                        <h2 class="h5">Incidents récents</h2>
                        <div class="list-group list-group-flush">
                            @forelse ($infrastructure->incidents as $incident)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between gap-2">
                                        <strong>{{ $incident->titre }}</strong>
                                        <span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span>
                                    </div>
                                    <div class="small text-secondary">{{ $incident->type }} - {{ $incident->gravite }} - {{ $incident->date_incident->format('d/m/Y') }}</div>
                                </div>
                            @empty
                                <p class="text-secondary mb-0">Aucun incident récent pour cette infrastructure.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
