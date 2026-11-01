@extends('layouts.admin')
@section('title', 'Utilisateurs')
@section('eyebrow', 'Administration')
@section('page-title', 'Gestion des utilisateurs')
@section('content')
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher un nom ou un email"></div>
    <div class="col-auto"><button class="btn btn-aqua">Rechercher</button></div>
</form>
<div class="card"><div class="table-responsive">
<table class="table align-middle mb-0">
    <thead><tr><th>Utilisateur</th><th>Rôle</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    @foreach ($users as $u)
        <tr>
            <td>
                <div class="d-flex align-items-center gap-2">
                    @include('partials.avatar', ['user' => $u, 'size' => 36])
                    <div><div class="fw-semibold">{{ $u->name }}</div><div class="small text-secondary">{{ $u->email }}</div></div>
                </div>
            </td>
            <td>
                @if ($u->is(auth()->user()))
                    <span class="badge text-bg-info">{{ $u->roleLabel() }} (vous)</span>
                @else
                    <form method="POST" action="{{ route('admin.users.role', $u) }}" class="d-flex gap-1">
                        @csrf @method('PUT')
                        <select name="role" class="form-select form-select-sm" style="width: 9rem">
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected($u->hasRole($role))>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-secondary">OK</button>
                    </form>
                @endif
            </td>
            <td>
                <span class="badge text-bg-{{ $u->actif ? 'success' : 'secondary' }}">{{ $u->actif ? 'Actif' : 'Désactivé' }}</span>
                @unless ($u->hasVerifiedEmail()) <span class="badge text-bg-warning">Email non vérifié</span> @endunless
            </td>
            <td class="text-end">
                @unless ($u->is(auth()->user()))
                    <form method="POST" action="{{ route('admin.users.actif', $u) }}" class="d-inline">
                        @csrf @method('PUT')
                        <button class="btn btn-sm btn-outline-warning">{{ $u->actif ? 'Désactiver' : 'Activer' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                @endunless
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
