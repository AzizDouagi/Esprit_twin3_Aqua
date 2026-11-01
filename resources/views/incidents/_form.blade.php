@csrf

<div class="row g-3">
    <div class="col-md-8">
        <label for="titre" class="form-label">Titre</label>
        <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre', $incident->titre ?? '') }}">
        @error('titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="date_incident" class="form-label">Date de l’incident</label>
        <input type="date" class="form-control @error('date_incident') is-invalid @enderror" id="date_incident" name="date_incident" value="{{ old('date_incident', isset($incident) && $incident->date_incident ? $incident->date_incident->format('Y-m-d') : now()->format('Y-m-d')) }}">
        @error('date_incident') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="infrastructure_id" class="form-label">Infrastructure liée</label>
        <select class="form-select @error('infrastructure_id') is-invalid @enderror" id="infrastructure_id" name="infrastructure_id">
            <option value="">Choisir...</option>
            @foreach ($infrastructures as $infrastructure)
                <option value="{{ $infrastructure->id }}" @selected((int) old('infrastructure_id', $incident->infrastructure_id ?? 0) === $infrastructure->id)>{{ $infrastructure->nom }}</option>
            @endforeach
        </select>
        @error('infrastructure_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-2">
        <label for="type" class="form-label">Type</label>
        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
            <option value="">Choisir...</option>
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('type', $incident->type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-2">
        <label for="gravite" class="form-label">Gravité</label>
        <select class="form-select @error('gravite') is-invalid @enderror" id="gravite" name="gravite">
            <option value="">Choisir...</option>
            @foreach ($gravites as $gravite)
                <option value="{{ $gravite }}" @selected(old('gravite', $incident->gravite ?? '') === $gravite)>{{ $gravite }}</option>
            @endforeach
        </select>
        @error('gravite') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-2">
        <label for="statut" class="form-label">Statut</label>
        <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut">
            <option value="">Choisir...</option>
            @foreach ($statuts as $statut)
                <option value="{{ $statut }}" @selected(old('statut', $incident->statut ?? 'Ouvert') === $statut)>{{ $statut }}</option>
            @endforeach
        </select>
        @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="technicien_id" class="form-label">Technicien assigné</label>
        <select class="form-select @error('technicien_id') is-invalid @enderror" id="technicien_id" name="technicien_id">
            <option value="">Non assigné</option>
            @foreach ($techniciens as $technicien)
                <option value="{{ $technicien->id }}" @selected((int) old('technicien_id', $incident->technicien_id ?? 0) === $technicien->id)>{{ $technicien->name }}</option>
            @endforeach
        </select>
        @error('technicien_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $incident->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-aqua"><i class="bi bi-save me-1"></i> Enregistrer</button>
    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
