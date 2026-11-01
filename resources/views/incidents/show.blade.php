@extends('layouts.admin')

@section('title', $incident->titre)
@section('page-title', $incident->titre)
@section('eyebrow', 'Détail incident')

@section('actions')
    @can('update', $incident)
        <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Modifier</a>
    @endcan
@endsection

@section('content')
    <section class="bg-white border rounded-2 p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-4"><div class="text-secondary small">Infrastructure</div><a class="fw-semibold" href="{{ route('infrastructures.show', $incident->infrastructure) }}">{{ $incident->infrastructure->nom }}</a></div>
            <div class="col-md-2"><div class="text-secondary small">Type</div><div>{{ $incident->type }}</div></div>
            <div class="col-md-2"><div class="text-secondary small">Gravité</div><span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span></div>
            <div class="col-md-2"><div class="text-secondary small">Statut</div><span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span></div>
            <div class="col-md-2"><div class="text-secondary small">Date</div><div>{{ $incident->date_incident->format('d/m/Y') }}</div></div>
            <div class="col-md-4"><div class="text-secondary small">Déclaré par</div><div>{{ $incident->declarant?->name ?? '—' }}</div></div>
            <div class="col-md-4"><div class="text-secondary small">Technicien assigné</div><div>{{ $incident->technicien?->name ?? 'Non assigné' }}</div></div>
            <div class="col-md-4"><div class="text-secondary small">Résolu le</div><div>{{ $incident->resolu_le?->format('d/m/Y H:i') ?? '—' }}</div></div>
            <div class="col-12"><div class="text-secondary small">Description</div><p class="mb-0">{{ $incident->description }}</p></div>
        </div>
        @stack('incident-ai')
    </section>

    <div class="row g-4">
        <div class="col-lg-7">
            @can('update', $incident)
            <section class="bg-white border rounded-2 p-4 mb-4">
                <h2 class="h5 mb-3"><i class="bi bi-diagram-3 me-1"></i>Workflow et assignation</h2>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @forelse ($incident->transitionsPossibles() as $statut)
                        <form method="POST" action="{{ route('incidents.statut', $incident) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut" value="{{ $statut }}">
                            <button class="btn btn-sm btn-outline-primary">Passer à « {{ $statut }} »</button>
                        </form>
                    @empty
                        <span class="text-secondary small">Aucune transition disponible.</span>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('incidents.assigner', $incident) }}" class="d-flex gap-2">
                    @csrf @method('PATCH')
                    <select name="technicien_id" class="form-select">
                        <option value="">Non assigné</option>
                        @foreach ($techniciens as $t)
                            <option value="{{ $t->id }}" @selected($incident->technicien_id === $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-aqua">Assigner</button>
                </form>
            </section>
            @endcan

            <section class="bg-white border rounded-2 p-4 mb-4">
                <h2 class="h5 mb-3"><i class="bi bi-chat-dots me-1"></i>Commentaires ({{ $incident->commentaires->count() }})</h2>
                <form method="POST" action="{{ route('incidents.commentaires.store', $incident) }}" class="mb-3">
                    @csrf
                    <textarea name="contenu" rows="2" class="form-control mb-2" placeholder="Ajouter un commentaire…" required>{{ old('contenu') }}</textarea>
                    <button class="btn btn-sm btn-aqua">Publier</button>
                </form>
                @forelse ($incident->commentaires as $c)
                    <div class="border-top py-2">
                        <div class="small text-secondary"><strong>{{ $c->auteur?->name ?? 'Utilisateur supprimé' }}</strong> · {{ $c->created_at->format('d/m/Y H:i') }}</div>
                        <div>{{ $c->contenu }}</div>
                    </div>
                @empty
                    <p class="text-secondary small mb-0">Aucun commentaire.</p>
                @endforelse
            </section>
        </div>

        <div class="col-lg-5">
            <section class="bg-white border rounded-2 p-4 mb-4">
                <h2 class="h5 mb-3"><i class="bi bi-paperclip me-1"></i>Pièces jointes</h2>
                <form method="POST" action="{{ route('incidents.pieces-jointes.store', $incident) }}" enctype="multipart/form-data" class="d-flex gap-2 mb-3">
                    @csrf
                    <input type="file" name="fichiers[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf" class="form-control form-control-sm" required>
                    <button class="btn btn-sm btn-aqua">Ajouter</button>
                </form>
                @forelse ($incident->piecesJointes as $pj)
                    <div class="d-flex justify-content-between align-items-center border-top py-2">
                        <a href="{{ route('incidents.pieces-jointes.show', [$incident, $pj]) }}" target="_blank">
                            <i class="bi bi-{{ $pj->estImage() ? 'image' : 'file-earmark-pdf' }} me-1"></i>{{ $pj->nom_original }}
                        </a>
                        @can('update', $incident)
                            <form method="POST" action="{{ route('incidents.pieces-jointes.destroy', [$incident, $pj]) }}" onsubmit="return confirm('Supprimer ce fichier ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p class="text-secondary small mb-0">Aucune pièce jointe.</p>
                @endforelse
            </section>

            <section class="bg-white border rounded-2 p-4">
                <h2 class="h5 mb-3"><i class="bi bi-clock-history me-1"></i>Historique (audit)</h2>
                @forelse ($incident->historiques as $h)
                    <div class="border-top py-2 small">
                        <div class="text-secondary">{{ $h->created_at->format('d/m/Y H:i') }} · {{ $h->auteur?->name ?? 'Système' }}</div>
                        <strong>{{ $h->champ }}</strong> :
                        @if ($h->ancienne_valeur) {{ $h->ancienne_valeur }} → @endif {{ $h->nouvelle_valeur }}
                    </div>
                @empty
                    <p class="text-secondary small mb-0">Aucun changement enregistré.</p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
