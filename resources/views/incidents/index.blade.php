@extends('layouts.admin')

@section('title', 'Incidents')
@section('page-title', 'Incidents')
@section('eyebrow', 'Suivi opérationnel')

@section('actions')
    <a href="{{ route('incidents.create') }}" class="btn btn-aqua">
        <i class="bi bi-plus-circle me-1"></i> Ajouter
    </a>
@endsection

@section('content')
    <form method="GET" action="{{ route('incidents.index') }}" class="bg-white border rounded-2 p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Recherche</label>
                <input type="search" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Titre, description, type ou infrastructure">
            </div>
            <div class="col-md-3">
                <label for="statut" class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach ($statuts as $statut)
                        <option value="{{ $statut }}" @selected(request('statut') === $statut)>{{ $statut }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="gravite" class="form-label">Gravité</label>
                <select id="gravite" name="gravite" class="form-select">
                    <option value="">Toutes</option>
                    @foreach ($gravites as $gravite)
                        <option value="{{ $gravite }}" @selected(request('gravite') === $gravite)>{{ $gravite }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Filtrer</button>
            </div>
        </div>
    </form>

    <section class="bg-white border rounded-2 p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Infrastructure</th>
                        <th>Type</th>
                        <th>Gravité</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidents as $incident)
                        <tr>
                            <td class="fw-semibold">{{ $incident->titre }}</td>
                            <td>{{ $incident->infrastructure?->nom }}</td>
                            <td>{{ $incident->type }}</td>
                            <td><span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span></td>
                            <td><span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span></td>
                            <td>{{ $incident->date_incident->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('incidents.show', $incident) }}" title="Voir"><i class="bi bi-eye"></i></a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('incidents.edit', $incident) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('incidents.destroy', $incident) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet incident ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Aucun incident trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $incidents->links() }}
    </section>
@endsection
