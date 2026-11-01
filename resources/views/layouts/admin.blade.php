<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BackOffice') - AquaSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('head')
    <style>
        :root {
            --aqua-primary: #075985;
            --aqua-secondary: #0891b2;
            --aqua-soft: #eef9fc;
            --aqua-ink: #0f172a;
        }
        body { background: #f7fafc; color: var(--aqua-ink); }
        .navbar-aqua { background: linear-gradient(90deg, var(--aqua-primary), var(--aqua-secondary)); }
        .brand-mark {
            width: 2rem; height: 2rem; display: inline-grid; place-items: center;
            background: rgba(255, 255, 255, .18); border-radius: .5rem;
        }
        .sidebar {
            background: #fff; border-right: 1px solid #e5e7eb; min-height: calc(100vh - 56px);
        }
        .sidebar .nav-link { color: #334155; border-radius: .5rem; margin-bottom: .25rem; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background: var(--aqua-soft); color: var(--aqua-primary); }
        .metric {
            border: 1px solid #e2e8f0; border-left: .3rem solid var(--aqua-secondary);
            background: #fff; border-radius: .5rem; padding: 1rem;
        }
        .table thead th { color: #475569; font-size: .82rem; text-transform: uppercase; }
        .btn-aqua { background: var(--aqua-primary); border-color: var(--aqua-primary); color: #fff; }
        .btn-aqua:hover { background: #036082; border-color: #036082; color: #fff; }
        .page-shell { max-width: 1180px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-aqua sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <span class="brand-mark"><i class="bi bi-droplet-half"></i></span>
                AquaSecure
            </a>
            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-sm btn-outline-light" href="{{ route('front.home') }}">
                    <i class="bi bi-globe2 me-1"></i> FrontOffice
                </a>
                @stack('navbar-extra')
                @include('partials.user-menu', ['light' => true])
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-lg-2 sidebar p-3">
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                    <a class="nav-link {{ request()->routeIs('infrastructures.*') ? 'active' : '' }}" href="{{ route('infrastructures.index') }}">
                        <i class="bi bi-buildings me-2"></i>Infrastructures
                    </a>
                    <a class="nav-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}" href="{{ route('incidents.index') }}">
                        <i class="bi bi-exclamation-octagon me-2"></i>Incidents
                    </a>
                    @can('maintenances.manage')
                        <a class="nav-link {{ request()->routeIs('maintenances.*') ? 'active' : '' }}" href="{{ route('maintenances.index') }}">
                            <i class="bi bi-tools me-2"></i>Maintenance
                        </a>
                    @endcan
                    <a class="nav-link {{ request()->routeIs('carte') ? 'active' : '' }}" href="{{ route('carte') }}">
                        <i class="bi bi-geo-alt me-2"></i>Carte
                    </a>
                    @can('users.manage')
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people me-2"></i>Utilisateurs
                        </a>
                    @endcan
                    @stack('sidebar-extra')
                </nav>
            </aside>
            <main class="col-lg-10 py-4">
                <div class="page-shell mx-auto px-2 px-md-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <p class="text-uppercase text-info fw-bold small mb-1">@yield('eyebrow', 'BackOffice')</p>
                            <h1 class="h3 mb-0">@yield('page-title', 'AquaSecure')</h1>
                        </div>
                        <div>@yield('actions')</div>
                    </div>
                    @include('partials.alerts')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
