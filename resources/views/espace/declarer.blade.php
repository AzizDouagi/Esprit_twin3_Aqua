@extends('layouts.front')
@section('title', 'Déclarer un incident')
@section('content')
<div class="container py-5" style="max-width: 820px">
    <h1 class="h3 mb-1">Déclarer un incident</h1>
    <p class="text-secondary">Signalez une fuite, une coupure ou toute anomalie observée sur le réseau.</p>
    <form method="POST" action="{{ route('espace.declarer.store') }}" enctype="multipart/form-data" class="bg-white border rounded-2 p-4">
        @csrf
        <div class="row g-3">
            <div class="col-12"><label class="form-label" for="titre">Titre</label>
                <input id="titre" name="titre" class="form-control @error('titre') is-invalid @enderror" value="{{ old('titre') }}" required></div>
            <div class="col-md-6"><label class="form-label" for="infrastructure_id">Infrastructure concernée</label>
                <select id="infrastructure_id" name="infrastructure_id" class="form-select @error('infrastructure_id') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    @foreach ($infrastructures as $i)<option value="{{ $i->id }}" @selected(old('infrastructure_id', request('infrastructure_id')) == $i->id)>{{ $i->nom }}</option>@endforeach
                </select></div>
            <div class="col-md-3"><label class="form-label" for="type">Type</label>
                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    @foreach ($types as $t)<option @selected(old('type') === $t)>{{ $t }}</option>@endforeach
                </select></div>
            <div class="col-md-3"><label class="form-label" for="gravite">Gravité estimée</label>
                <select id="gravite" name="gravite" class="form-select @error('gravite') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    @foreach ($gravites as $g)<option @selected(old('gravite') === $g)>{{ $g }}</option>@endforeach
                </select></div>
            <div class="col-12"><label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                @include('ai.classify-button', ['formulaire' => 'declaration'])</div>
            <div class="col-12"><label class="form-label" for="photos">Photos (3 max, 4 Mo chacune)</label>
                <input type="file" id="photos" name="photos[]" accept="image/*" multiple class="form-control"></div>
        </div>
        <button class="btn btn-aqua mt-4"><i class="bi bi-send me-1"></i>Envoyer le signalement</button>
    </form>
</div>
@endsection
