@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $infrastructure->nom ?? '') }}">
        @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="localisation" class="form-label">Localisation</label>
        <input type="text" class="form-control @error('localisation') is-invalid @enderror" id="localisation" name="localisation" value="{{ old('localisation', $infrastructure->localisation ?? '') }}">
        @error('localisation') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="type" class="form-label">Type</label>
        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
            <option value="">Choisir...</option>
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('type', $infrastructure->type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="etat" class="form-label">État</label>
        <select class="form-select @error('etat') is-invalid @enderror" id="etat" name="etat">
            <option value="">Choisir...</option>
            @foreach ($etats as $etat)
                <option value="{{ $etat }}" @selected(old('etat', $infrastructure->etat ?? '') === $etat)>{{ $etat }}</option>
            @endforeach
        </select>
        @error('etat') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="date_installation" class="form-label">Date d’installation</label>
        <input type="date" class="form-control @error('date_installation') is-invalid @enderror" id="date_installation" name="date_installation" value="{{ old('date_installation', isset($infrastructure) && $infrastructure->date_installation ? $infrastructure->date_installation->format('Y-m-d') : '') }}">
        @error('date_installation') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $infrastructure->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-aqua"><i class="bi bi-save me-1"></i> Enregistrer</button>
    <a href="{{ route('infrastructures.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
