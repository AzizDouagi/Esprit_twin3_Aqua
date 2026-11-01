@extends('layouts.admin')
@section('title', 'Maintenance')
@section('eyebrow', 'Planning')
@section('page-title', 'Planning de maintenance')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <form method="GET" class="bg-white border rounded-2 p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label">Statut</label>
                    <select name="statut" class="form-select"><option value="">Tous</option>
                        @foreach ($statuts as $s)<option @selected(request('statut') === $s)>{{ $s }}</option>@endforeach
                    </select></div>
                <div class="col-md-3"><label class="form-label">Infrastructure</label>
                    <select name="infrastructure_id" class="form-select"><option value="">Toutes</option>
                        @foreach ($infrastructures as $i)<option value="{{ $i->id }}" @selected(request('infrastructure_id') == $i->id)>{{ $i->nom }}</option>@endforeach
                    </select></div>
                <div class="col-md-3"><label class="form-label">Technicien</label>
                    <select name="technicien_id" class="form-select"><option value="">Tous</option>
                        @foreach ($techniciens as $t)<option value="{{ $t->id }}" @selected(request('technicien_id') == $t->id)>{{ $t->name }}</option>@endforeach
                    </select></div>
                <div class="col-md-2"><label class="form-label">Mois</label><input type="month" name="mois" value="{{ request('mois') }}" class="form-control"></div>
                <div class="col-md-1 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search"></i></button></div>
            </div>
        </form>
    </div>
    <div class="col-lg-4"><div class="metric h-100"><div class="text-secondary small">Coût des maintenances réalisées</div><div class="fs-3 fw-bold">{{ number_format($coutTotal, 2, ',', ' ') }} DT</div></div></div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="bg-white border rounded-2 p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Date prévue</th><th>Intervention</th><th>Infrastructure</th><th>Technicien</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse ($maintenances as $m)
                        <tr>
                            <td>{{ $m->date_prevue->format('d/m/Y') }}</td>
                            <td>{{ $m->titre }}@if($m->cout)<div class="small text-secondary">{{ number_format($m->cout, 2, ',', ' ') }} DT</div>@endif</td>
                            <td>{{ $m->infrastructure?->nom }}</td>
                            <td>{{ $m->technicien?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-{{ $m->badge() }}">{{ $m->enRetard() ? 'En retard' : $m->statut }}</span></td>
                            <td class="text-end">
                                @if ($m->statut === 'Planifiée')
                                    <form method="POST" action="{{ route('maintenances.update', $m) }}" class="d-inline-flex gap-1">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="statut" value="Réalisée">
                                        <input type="number" step="0.01" min="0" name="cout" class="form-control form-control-sm" style="width:6rem" placeholder="Coût">
                                        <button class="btn btn-sm btn-outline-success" title="Marquer réalisée"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('maintenances.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Aucune maintenance.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $maintenances->links() }}
        </section>
    </div>
    <div class="col-xl-4">
        <section class="bg-white border rounded-2 p-3">
            <h2 class="h5">Planifier une maintenance</h2>
            <form method="POST" action="{{ route('maintenances.store') }}">
                @csrf
                <div class="mb-2"><label class="form-label">Infrastructure</label>
                    <select name="infrastructure_id" class="form-select" required>
                        @foreach ($infrastructures as $i)<option value="{{ $i->id }}">{{ $i->nom }}</option>@endforeach
                    </select></div>
                <div class="mb-2"><label class="form-label">Intervention</label><input name="titre" class="form-control" value="{{ old('titre') }}" required></div>
                <div class="mb-2"><label class="form-label">Date prévue</label><input type="date" name="date_prevue" class="form-control" value="{{ old('date_prevue') }}" required></div>
                <div class="mb-2"><label class="form-label">Technicien</label>
                    <select name="technicien_id" class="form-select"><option value="">—</option>
                        @foreach ($techniciens as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea></div>
                <button class="btn btn-aqua w-100">Planifier</button>
            </form>
        </section>
    </div>
</div>
@endsection
