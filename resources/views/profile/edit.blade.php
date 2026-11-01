@extends(auth()->user()->accesBackOffice() ? 'layouts.admin' : 'layouts.front')
@section('title', 'Mon profil')
@section('eyebrow', 'Compte')
@section('page-title', 'Mon profil')
@section('content')
<div class="{{ auth()->user()->accesBackOffice() ? '' : 'container py-4' }}">
    @include('profile._tabs')
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card mb-4"><div class="card-body">
                <h2 class="h5 mb-3">Photo de profil</h2>
                <div class="d-flex align-items-center gap-3 mb-3">
                    @include('partials.avatar', ['user' => $user, 'size' => 72])
                    <div class="small text-secondary">JPG, PNG ou WebP — 2 Mo maximum.</div>
                </div>
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="d-flex gap-2">
                    @csrf
                    <input type="file" name="avatar" accept="image/*" class="form-control" required>
                    <button class="btn btn-aqua">Envoyer</button>
                </form>
                @if ($user->avatar_path)
                    <form method="POST" action="{{ route('profile.avatar.destroy') }}" class="mt-2">
                        @csrf @method('DELETE')
                        <button class="btn btn-link btn-sm text-danger p-0">Supprimer la photo</button>
                    </form>
                @endif
            </div></div>

            <div class="card"><div class="card-body">
                <h2 class="h5 mb-3">Informations personnelles</h2>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="name">Nom</label>
                        <input id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Adresse email</label>
                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        <div class="form-text">Changer d’email nécessite une nouvelle vérification.</div>
                    </div>
                    <button class="btn btn-aqua">Enregistrer</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4"><div class="card-body">
                <h2 class="h5 mb-3">Changer le mot de passe</h2>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Mot de passe actuel</label>
                        <input id="current_password" type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_password">Nouveau mot de passe</label>
                        <input id="new_password" type="password" name="password" class="form-control" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_password_confirmation">Confirmation</label>
                        <input id="new_password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-aqua">Mettre à jour</button>
                </form>
            </div></div>

            <div class="card border-danger"><div class="card-body">
                <h2 class="h5 text-danger mb-2">Supprimer mon compte</h2>
                <p class="small text-secondary">Action irréversible. Confirmez avec votre mot de passe.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Supprimer définitivement votre compte ?');">
                    @csrf @method('DELETE')
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Mot de passe" required autocomplete="current-password">
                    </div>
                    <button class="btn btn-danger"><i class="bi bi-trash me-1"></i>Supprimer mon compte</button>
                </form>
            </div></div>
        </div>
    </div>
</div>
@endsection
