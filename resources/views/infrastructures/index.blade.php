@extends('layouts.admin')

@section('title', 'Infrastructures')
@section('page-title', 'Infrastructures')
@section('eyebrow', 'Gestion du réseau')

@section('actions')
    <a href="{{ route('infrastructures.create') }}" class="btn btn-aqua">
        <i class="bi bi-plus-circle me-1"></i> Ajouter
    </a>
@endsection

@section('content')
    <form method="GET" action="{{ route('infrastructures.index') }}" class="bg-white border rounded-2 p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-7">
                <label for="search" class="form-label">Recherche</label>
                <input type="search" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, type ou localisation">
            </div>
            <div class="col-md-3">
                <label for="etat" class="form-label">État</label>
                <select id="etat" name="etat" class="form-select">
                    <option value="">Tous</option>
                    @foreach ($etats as $etat)
                        <option value="{{ $etat }}" @selected(request('etat') === $etat)>{{ $etat }}</option>
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
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Localisation</th>
                        <th>État</th>
                        <th>Incidents</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($infrastructures as $infrastructure)
                        <tr>
                            <td class="fw-semibold">{{ $infrastructure->nom }}</td>
                            <td>{{ $infrastructure->type }}</td>
                            <td>{{ $infrastructure->localisation }}</td>
                            <td><span class="badge text-bg-{{ $infrastructure->statut_badge }}">{{ $infrastructure->etat }}</span></td>
                            <td>{{ $infrastructure->incidents_count }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('infrastructures.show', $infrastructure) }}" title="Voir"><i class="bi bi-eye"></i></a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('infrastructures.edit', $infrastructure) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('infrastructures.destroy', $infrastructure) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette infrastructure et ses incidents associés ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Aucune infrastructure trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $infrastructures->links() }}
    </section>
@endsection
