@auth
    @php($u = auth()->user())
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle {{ $light ?? false ? 'text-white' : 'text-dark' }}" data-bs-toggle="dropdown" aria-expanded="false">
            @include('partials.avatar', ['user' => $u, 'size' => 32])
            <span class="d-none d-md-inline">{{ $u->name }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-secondary">{{ $u->roleLabel() }}</span></li>
            <li><a class="dropdown-item" href="{{ route('espace.index') }}"><i class="bi bi-person-workspace me-2"></i>Mon espace</a></li>
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>Mon profil</a></li>
            <li><a class="dropdown-item" href="{{ route('profile.security') }}"><i class="bi bi-shield-lock me-2"></i>Sécurité (2FA)</a></li>
            @if ($u->accesBackOffice())
                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>BackOffice</a></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</button>
                </form>
            </li>
        </ul>
    </div>
@else
    <a class="btn btn-sm {{ $light ?? false ? 'btn-outline-light' : 'btn-outline-secondary' }}" href="{{ route('login') }}">Connexion</a>
    @if (Route::has('register'))
        <a class="btn btn-sm btn-aqua" href="{{ route('register') }}">Inscription</a>
    @endif
@endauth
