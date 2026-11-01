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

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="metric">
                <div class="text-secondary small">MTTR (résolution moyenne)</div>
                <div class="fs-2 fw-bold">{{ $mttr !== null ? $mttr.' h' : '—' }}</div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="bg-white border rounded-2 p-3 h-100">
                <h2 class="h6">Incidents par mois</h2>
                <canvas id="chart-mois" height="110"></canvas>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="bg-white border rounded-2 p-3 h-100">
                <h2 class="h6">Incidents par gravité</h2>
                <canvas id="chart-gravite" height="110"></canvas>
            </div>
        </div>
    </div>

    <section class="bg-white border rounded-2 p-3 mb-4">
        <h2 class="h5 mb-3"><i class="bi bi-exclamation-triangle me-1"></i> Top infrastructures à risque</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Infrastructure</th><th>Score</th><th>Niveau</th><th>Principal facteur</th></tr></thead>
                <tbody>
                    @foreach ($topRisque as $infra)
                        @php $f = collect($infra->risque['facteurs'])->sortByDesc('points')->first(); @endphp
                        <tr>
                            <td><a href="{{ route('infrastructures.show', $infra) }}">{{ $infra->nom }}</a></td>
                            <td style="min-width:160px">
                                <div class="progress" style="height:8px"><div class="progress-bar bg-{{ $infra->risque['couleur'] }}" style="width: {{ $infra->risque['score'] }}%"></div></div>
                                <small>{{ $infra->risque['score'] }}/100</small>
                            </td>
                            <td><span class="badge text-bg-{{ $infra->risque['couleur'] }}">{{ $infra->risque['niveau'] }}</span></td>
                            <td class="small text-secondary">{{ $f['libelle'] }} ({{ $f['detail'] }})</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('chart-mois'), {
            type: 'bar',
            data: { labels: @json(array_keys($parMois)), datasets: [{ label: 'Incidents', data: @json(array_values($parMois)), backgroundColor: '#0d6efd' }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
        new Chart(document.getElementById('chart-gravite'), {
            type: 'doughnut',
            data: { labels: @json(array_keys($parGravite)), datasets: [{ data: @json(array_values($parGravite)), backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545'] }] },
        });
    </script>
@endpush
@endsection
