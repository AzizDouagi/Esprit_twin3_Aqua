@extends('layouts.admin')

@section('title', $infrastructure->nom)
@section('page-title', $infrastructure->nom)
@section('eyebrow', 'Détail infrastructure')

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('infrastructures.edit', $infrastructure) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Modifier</a>
        <a href="{{ route('incidents.create', ['infrastructure_id' => $infrastructure->id]) }}" class="btn btn-aqua"><i class="bi bi-plus-circle me-1"></i> Incident</a>
    </div>
@endsection

@section('content')
    <section class="bg-white border rounded-2 p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-4"><div class="text-secondary small">Type</div><div class="fw-semibold">{{ $infrastructure->type }}</div></div>
            <div class="col-md-4"><div class="text-secondary small">Localisation</div><div class="fw-semibold">{{ $infrastructure->localisation }}</div></div>
            <div class="col-md-4"><div class="text-secondary small">État</div><span class="badge text-bg-{{ $infrastructure->statut_badge }}">{{ $infrastructure->etat }}</span></div>
            <div class="col-md-4"><div class="text-secondary small">Date d’installation</div><div>{{ $infrastructure->date_installation?->format('d/m/Y') ?? 'Non renseignée' }}</div></div>
            <div class="col-12"><div class="text-secondary small">Description</div><p class="mb-0">{{ $infrastructure->description ?: 'Aucune description.' }}</p></div>
        </div>
    </section>

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
