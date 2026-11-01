@extends('layouts.front')
@section('title', $incident->titre)
@section('content')
<div class="container py-5">
    <a href="{{ route('espace.signalements') }}" class="small"><i class="bi bi-arrow-left"></i> Mes signalements</a>
    <h1 class="h3 mt-2">{{ $incident->titre }}</h1>
    <div class="mb-3">
        <span class="badge text-bg-{{ $incident->graviteBadge() }}">{{ $incident->gravite }}</span>
        <span class="badge text-bg-{{ $incident->statutBadge() }}">{{ $incident->statut }}</span>
        <span class="text-secondary small ms-2">{{ $incident->infrastructure?->nom }} · {{ $incident->date_incident->format('d/m/Y') }}</span>
    </div>
    <div class="info-tile mb-4"><p class="mb-0">{{ $incident->description }}</p></div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="info-tile">
                <h2 class="h5">Échanges</h2>
                <form method="POST" action="{{ route('incidents.commentaires.store', $incident) }}" class="mb-3">@csrf
                    <textarea name="contenu" rows="2" class="form-control mb-2" placeholder="Ajouter une précision…" required></textarea>
                    <button class="btn btn-sm btn-aqua">Envoyer</button></form>
                @forelse ($incident->commentaires as $c)
                    <div class="border-top py-2"><div class="small text-secondary"><strong>{{ $c->auteur?->name }}</strong> · {{ $c->created_at->format('d/m/Y H:i') }}</div>{{ $c->contenu }}</div>
                @empty <p class="small text-secondary mb-0">Aucun échange.</p> @endforelse
            </div>
        </div>
        <div class="col-lg-5">
            <div class="info-tile">
                <h2 class="h5">Suivi</h2>
                @forelse ($incident->historiques as $h)
                    <div class="border-top py-2 small"><span class="text-secondary">{{ $h->created_at->format('d/m/Y H:i') }}</span> · {{ $h->champ }} @if($h->champ !== 'Création' && $h->champ !== 'Technicien'): {{ $h->ancienne_valeur }} → {{ $h->nouvelle_valeur }} @endif</div>
                @empty <p class="small text-secondary mb-0">Pas encore de suivi.</p> @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
