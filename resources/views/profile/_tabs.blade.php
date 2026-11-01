<ul class="nav nav-pills mb-4">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person me-1"></i>Informations</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile.security') ? 'active' : '' }}" href="{{ route('profile.security') }}"><i class="bi bi-shield-lock me-1"></i>Sécurité (2FA)</a></li>
</ul>
