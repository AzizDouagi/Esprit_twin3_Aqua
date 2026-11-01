@extends('layouts.admin')

@section('title', 'Incidents')
@section('page-title', 'Incidents')
@section('eyebrow', 'Suivi opérationnel')

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('incidents.export-csv', request()->query()) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv me-1"></i> CSV</a>
        <a href="{{ route('incidents.export-pdf', request()->query()) }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
        <a href="{{ route('incidents.create') }}" class="btn btn-aqua"><i class="bi bi-plus-circle me-1"></i> Ajouter</a>
    </div>
@endsection

@section('content')
    <form method="GET" action="{{ route('incidents.index') }}" class="bg-white border rounded-2 p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="search" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Titre, description, type ou infrastructure">
            </div>
            <div class="col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach ($statuts as $statut)<option value="{{ $statut }}" @selected(request('statut') === $statut)>{{ $statut }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="gravite" class="form-label">Gravité</label>
                <select id="gravite" name="gravite" class="form-select">
                    <option value="">Toutes</option>
                    @foreach ($gravites as $gravite)<option value="{{ $gravite }}" @selected(request('gravite') === $gravite)>{{ $gravite }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="infrastructure_id" class="form-label">Infrastructure</label>
                <select id="infrastructure_id" name="infrastructure_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach ($infrastructures as $i)<option value="{{ $i->id }}" @selected(request('infrastructure_id') == $i->id)>{{ $i->nom }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="technicien_id" class="form-label">Technicien</label>
                <select id="technicien_id" name="technicien_id" class="form-select">
                    <option value="">Tous</option>
                    <option value="none" @selected(request('technicien_id') === 'none')>Non assigné</option>
                    @foreach ($techniciens as $t)<option value="{{ $t->id }}" @selected(request('technicien_id') == $t->id)>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><label for="du" class="form-label">Du</label><input type="date" id="du" name="du" class="form-control" value="{{ request('du') }}"></div>
            <div class="col-md-2"><label for="au" class="form-label">Au</label><input type="date" id="au" name="au" class="form-control" value="{{ request('au') }}"></div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search me-1"></i> Filtrer</button>
                <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bi bi-x-lg"></i></a>
            </div>
        </div>
    </form>

    <section class="bg-white border rounded-2 p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th><a class="text-decoration-none text-secondary" href="{{ request()->fullUrlWithQuery(['tri' => 'titre', 'sens' => request('tri') === 'titre' && request('sens') === 'asc' ? 'desc' : 'asc']) }}">Titre <i class="bi bi-arrow-down-up small"></i></a></th>
                        <th>Infrastructure</th>
                        <th>Type</th>
                        <th>Technicien</th>
                        <th><a class="text-decoration-none text-secondary" href="{{ request()->fullUrlWithQuery(['tri' => 'gravite', 'sens' => request('tri') === 'gravite' && request('sens') === 'asc' ? 'desc' : 'asc']) }}">Gravité <i class="bi bi-arrow-down-up small"></i></a></th>
                        <th><a class="text-decoration-none text-secondary" href="{{ request()->fullUrlWithQuery(['tri' => 'statut', 'sens' => request('tri') === 'statut' && request('sens') === 'asc' ? 'desc' : 'asc']) }}">Statut <i class="bi bi-arrow-down-up small"></i></a></th>
                        <th><a class="text-decoration-none text-secondary" href="{{ request()->fullUrlWithQuery(['tri' => 'date_incident', 'sens' => request('tri') === 'date_incident' && request('sens') === 'asc' ? 'desc' : 'asc']) }}">Date <i class="bi bi-arrow-down-up small"></i></a></th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidents as $incident)
                        <tr>
                            <td class="fw-semibold">{{ $incident->titre }}</td>
                            <td>{{ $incident->infrastructure?->nom }}</td>
                            <td>{{ $incident->type }}</td>
                            <td>{{ $incident->technicien?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span></td>
                            <td><span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span></td>
                            <td>{{ $incident->date_incident->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('incidents.show', $incident) }}" title="Voir"><i class="bi bi-eye"></i></a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('incidents.edit', $incident) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                                @can('delete', $incident)
                                <form action="{{ route('incidents.destroy', $incident) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet incident ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary py-4">Aucun incident trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $incidents->links() }}
    </section>
@endsection
