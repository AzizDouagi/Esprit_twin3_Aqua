@extends(auth()->user()->accesBackOffice() ? 'layouts.admin' : 'layouts.front')
@section('title', 'Sécurité')
@section('eyebrow', 'Compte')
@section('page-title', 'Sécurité du compte')
@section('content')
<div class="{{ auth()->user()->accesBackOffice() ? '' : 'container py-4' }}">
    @include('profile._tabs')
    <div class="card" style="max-width: 720px"><div class="card-body">
        <h2 class="h5">Authentification à deux facteurs (TOTP)</h2>

        @if (! $enabled)
            <p class="text-secondary">La double authentification est <strong>désactivée</strong>. Activez-la pour protéger votre compte avec une application comme Google Authenticator, Authy ou 1Password.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                <button class="btn btn-aqua"><i class="bi bi-shield-plus me-1"></i>Activer la 2FA</button>
            </form>
        @elseif (! $confirmed)
            <p class="text-secondary">1. Scannez ce QR code avec votre application d’authentification. 2. Saisissez le code à 6 chiffres pour confirmer l’activation.</p>
            <div class="mb-3 p-2 bg-white d-inline-block border rounded">{!! $user->twoFactorQrCodeSvg() !!}</div>
            <p class="small text-secondary">Clé de configuration manuelle : <code>{{ decrypt($user->two_factor_secret) }}</code></p>
            <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="d-flex gap-2 mb-3" style="max-width: 360px">
                @csrf
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" class="form-control" placeholder="Code à 6 chiffres" required>
                <button class="btn btn-aqua">Confirmer</button>
            </form>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf @method('DELETE')
                <button class="btn btn-link btn-sm text-danger p-0">Annuler l’activation</button>
            </form>
        @else
            <div class="alert alert-success"><i class="bi bi-shield-check me-1"></i>La double authentification est <strong>activée</strong>.</div>
            <h3 class="h6">Codes de récupération</h3>
            <p class="small text-secondary">Conservez-les en lieu sûr : chaque code ne sert qu’une seule fois si vous perdez votre téléphone.</p>
            <ul class="list-unstyled font-monospace bg-light border rounded p-3">
                @foreach ($user->recoveryCodes() as $code)
                    <li>{{ $code }}</li>
                @endforeach
            </ul>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}">
                    @csrf
                    <button class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat me-1"></i>Régénérer les codes</button>
                </form>
                <form method="POST" action="{{ url('/user/two-factor-authentication') }}" onsubmit="return confirm('Désactiver la double authentification ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">Désactiver la 2FA</button>
                </form>
            </div>
        @endif
    </div></div>
</div>
@endsection
