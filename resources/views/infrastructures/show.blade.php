@extends('layouts.admin')

@section('title', $infrastructure->nom)
@section('page-title', $infrastructure->nom)
@section('eyebrow', 'Détail infrastructure')

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('infrastructures.fiche-pdf', $infrastructure) }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-pdf me-1"></i> Fiche PDF</a>
        @can('update', $infrastructure)
            <a href="{{ route('infrastructures.edit', $infrastructure) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Modifier</a>
        @endcan
        <a href="{{ route('incidents.create', ['infrastructure_id' => $infrastructure->id]) }}" class="btn btn-aqua"><i class="bi bi-plus-circle me-1"></i> Incident</a>
    </div>
@endsection

@section('content')
    @can('ai.use')<div class="d-flex flex-wrap gap-2 mb-3"><a class="btn btn-outline-primary" href="{{ route('ai.risk', $infrastructure) }}">Score de risque</a><a class="btn btn-outline-primary" href="{{ route('ai.report', $infrastructure) }}">Rapport mensuel IA</a></div>@endcan
    <section class="bg-white border rounded-2 p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-4"><div class="text-secondary small">Type</div><div class="fw-semibold">{{ $infrastructure->type }}</div></div>
            <div class="col-md-4"><div class="text-secondary small">Localisation</div><div class="fw-semibold">{{ $infrastructure->localisation }}</div></div>
            <div class="col-md-4"><div class="text-secondary small">État</div><span class="badge text-bg-{{ $infrastructure->statut_badge }}">{{ $infrastructure->etat }}</span></div>
            <div class="col-md-4"><div class="text-secondary small">Date d’installation</div><div>{{ $infrastructure->date_installation?->format('d/m/Y') ?? 'Non renseignée' }}</div></div>
            <div class="col-12"><div class="text-secondary small">Description</div><p class="mb-0">{{ $infrastructure->description ?: 'Aucune description.' }}</p></div>
        </div>
    </section>

    <section class="bg-white border rounded-2 p-4 mb-4">
        <h2 class="h5 mb-3"><i class="bi bi-heart-pulse me-1"></i>Indicateurs de santé (dernier relevé)</h2>
        <div class="row g-3">
            @foreach ($indicateurs as $ind)
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="metric" style="border-left-color: var(--bs-{{ $ind['statut'] }})">
                        <div class="text-secondary small">{{ $ind['libelle'] }}</div>
                        <div class="fs-5 fw-bold">{{ $ind['valeur'] ?? '—' }} <small class="text-secondary fs-6">{{ $ind['unite'] }}</small></div>
                        <div class="small text-secondary">moy. 24 h : {{ $ind['moyenne24h'] ?? '—' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-white border rounded-2 p-4 mb-4">
        <h2 class="h5 mb-3"><i class="bi bi-graph-up me-1"></i>Mesures des 30 derniers jours (moyenne journalière)</h2>
        @if (count($serie['labels']))
            <div class="row g-4">
                @foreach ($grandeurs as $cle => [$libelle, $unite])
                    <div class="col-lg-6"><div class="small fw-semibold mb-1">{{ $libelle }} {{ $unite ? '('.$unite.')' : '' }}</div><canvas id="chart-{{ $cle }}" height="110"></canvas></div>
                @endforeach
            </div>
        @else
            <p class="text-secondary mb-0">Aucune mesure disponible pour cette infrastructure.</p>
        @endif
    </section>

    @if ($maintenances->isNotEmpty())
        <section class="bg-white border rounded-2 p-4 mb-4">
            <h2 class="h5 mb-3"><i class="bi bi-tools me-1"></i>Maintenances récentes</h2>
            <ul class="list-group list-group-flush">
                @foreach ($maintenances as $m)
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span>{{ $m->date_prevue->format('d/m/Y') }} — {{ $m->titre }}</span>
                        <span class="badge text-bg-{{ $m->badge() }}">{{ $m->statut }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="bg-white border rounded-2 p-3">
        <h2 class="h5 mb-3">Incidents associés</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Titre</th><th>Type</th><th>Gravité</th><th>Statut</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($infrastructure->incidents as $incident)
                        <tr>
                            <td>{{ $incident->titre }}</td>
                            <td>{{ $incident->type }}</td>
                            <td><span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span></td>
                            <td><span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span></td>
                            <td>{{ $incident->date_incident->format('d/m/Y') }}</td>
                            <td class="text-end"><a href="{{ route('incidents.show', $incident) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Aucun incident lié.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@if (count($serie['labels']))
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            const serie = @json($serie);
            const seuils = @json(config('aquasecure.seuils'));
            const couleurs = {debit: '#0891b2', pression: '#7c3aed', niveau: '#075985', turbidite: '#dc2626', ph: '#16a34a', temperature: '#ea580c'};
            Object.keys(serie.series).forEach(cle => {
                const [min, max] = seuils[cle];
                new Chart(document.getElementById('chart-' + cle), {
                    type: 'line',
                    data: {labels: serie.labels, datasets: [
                        {label: cle, data: serie.series[cle], borderColor: couleurs[cle], backgroundColor: couleurs[cle] + '22', fill: true, tension: .3, pointRadius: 2},
                        {label: 'Seuil max', data: serie.labels.map(() => max), borderColor: '#dc3545', borderDash: [6, 4], pointRadius: 0, borderWidth: 1},
                        {label: 'Seuil min', data: serie.labels.map(() => min), borderColor: '#ffc107', borderDash: [6, 4], pointRadius: 0, borderWidth: 1},
                    ]},
                    options: {plugins: {legend: {display: false}}, scales: {x: {ticks: {maxTicksLimit: 8}}}},
                });
            });
        </script>
    @endpush
@endif
