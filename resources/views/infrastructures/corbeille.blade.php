@extends('layouts.admin')
@section('title', 'Corbeille')
@section('eyebrow', 'Infrastructures')
@section('page-title', 'Corbeille des infrastructures')
@section('actions')
    <a href="{{ route('infrastructures.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour à la liste</a>
@endsection
@section('content')
<section class="bg-white border rounded-2 p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Nom</th><th>Type</th><th>Supprimée le</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($infrastructures as $infra)
                <tr>
                    <td>{{ $infra->nom }}</td>
                    <td>{{ $infra->type }}</td>
                    <td>{{ $infra->deleted_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('infrastructures.restore', $infra->id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Restaurer</button>
                        </form>
                        <form method="POST" action="{{ route('infrastructures.force-delete', $infra->id) }}" class="d-inline" onsubmit="return confirm('Suppression définitive (incidents liés inclus) ?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-octagon"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-secondary py-4">La corbeille est vide.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $infrastructures->links() }}
</section>
@endsection
