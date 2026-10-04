@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Tableau de bord')
@section('eyebrow', 'Pilotage AquaSecure')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="metric">
                <div class="text-secondary small">Total infrastructures</div>
                <div class="fs-2 fw-bold">{{ $totalInfrastructures }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="metric">
                <div class="text-secondary small">Total incidents</div>
                <div class="fs-2 fw-bold">{{ $totalIncidents }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="metric">
                <div class="text-secondary small">Incidents ouverts</div>
                <div class="fs-2 fw-bold text-danger">{{ $incidentsOuverts }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="metric">
                <div class="text-secondary small">Incidents critiques</div>
                <div class="fs-2 fw-bold text-warning">{{ $incidentsCritiques }}</div>
            </div>
        </div>
    </div>

    <section class="bg-white border rounded-2 p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0">Derniers incidents</h2>
            <a href="{{ route('incidents.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-list-ul me-1"></i> Voir tout
            </a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Infrastructure</th>
                        <th>Gravité</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($derniersIncidents as $incident)
                        <tr>
                            <td><a href="{{ route('incidents.show', $incident) }}">{{ $incident->titre }}</a></td>
                            <td>{{ $incident->infrastructure?->nom }}</td>
                            <td><span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span></td>
                            <td><span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span></td>
                            <td>{{ $incident->date_incident->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Aucun incident enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
