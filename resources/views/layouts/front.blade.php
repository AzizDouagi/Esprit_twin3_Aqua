<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Accueil') - AquaSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('head')
    <style>
        :root { --aqua-primary: #075985; --aqua-secondary: #0891b2; --aqua-soft: #ecfeff; }
        body { background: #f8fafc; color: #0f172a; }
        .front-nav { background: #fff; border-bottom: 1px solid #e2e8f0; }
        .brand-mark { width: 2rem; height: 2rem; display: inline-grid; place-items: center; color: #fff; background: var(--aqua-primary); border-radius: .5rem; }
        .hero {
            background:
                linear-gradient(120deg, rgba(7, 89, 133, .94), rgba(8, 145, 178, .78)),
                url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=80') center/cover;
            color: #fff;
        }
        .btn-aqua { background: var(--aqua-primary); border-color: var(--aqua-primary); color: #fff; }
        .btn-aqua:hover { background: #036082; border-color: #036082; color: #fff; }
        .soft-band { background: var(--aqua-soft); }
        .info-tile { background: #fff; border: 1px solid #e2e8f0; border-radius: .5rem; padding: 1rem; height: 100%; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg front-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('front.home') }}">
                <span class="brand-mark"><i class="bi bi-droplet-half"></i></span>
                AquaSecure
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#frontNav" aria-controls="frontNav" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="frontNav">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <a class="nav-link {{ request()->routeIs('front.home') ? 'active' : '' }}" href="{{ route('front.home') }}">Accueil</a>
                    <a class="nav-link {{ request()->routeIs('front.infrastructures.*') ? 'active' : '' }}" href="{{ route('front.infrastructures.index') }}">Infrastructures</a>
                    <a class="nav-link {{ request()->routeIs('carte') ? 'active' : '' }}" href="{{ route('carte') }}">Carte</a>
                    @auth
                        <a class="nav-link {{ request()->routeIs('espace.*') ? 'active' : '' }}" href="{{ route('espace.index') }}">Mon espace</a>
                        @can('admin.access')
                            <a class="btn btn-sm btn-aqua" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>BackOffice</a>
                        @endcan
                    @endauth
                    @stack('navbar-extra')
                    @include('partials.user-menu')
                </div>
            </div>
        </div>
    </nav>
    <div class="container pt-3">@include('partials.alerts')</div>
    @yield('content')
    <footer class="py-4 bg-white border-top">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small text-secondary">
            <span>AquaSecure - Surveillance des infrastructures d’eau potable</span>
            <span>Module Infrastructures & Incidents</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
