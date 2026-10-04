@extends('layouts.admin')

@section('title', $incident->titre)
@section('page-title', $incident->titre)
@section('eyebrow', 'Détail incident')

@section('actions')
    <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Modifier</a>
@endsection

@section('content')
    <section class="bg-white border rounded-2 p-4">
        <div class="row g-3">
            <div class="col-md-4"><div class="text-secondary small">Infrastructure</div><a class="fw-semibold" href="{{ route('infrastructures.show', $incident->infrastructure) }}">{{ $incident->infrastructure->nom }}</a></div>
            <div class="col-md-2"><div class="text-secondary small">Type</div><div>{{ $incident->type }}</div></div>
            <div class="col-md-2"><div class="text-secondary small">Gravité</div><span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span></div>
            <div class="col-md-2"><div class="text-secondary small">Statut</div><span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span></div>
            <div class="col-md-2"><div class="text-secondary small">Date</div><div>{{ $incident->date_incident->format('d/m/Y') }}</div></div>
            <div class="col-12"><div class="text-secondary small">Description</div><p class="mb-0">{{ $incident->description }}</p></div>
        </div>
    </section>
@endsection
