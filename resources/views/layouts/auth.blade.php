<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Connexion') - AquaSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --aqua-primary: #075985; --aqua-secondary: #0891b2; }
        body { min-height: 100vh; background: linear-gradient(135deg, var(--aqua-primary), var(--aqua-secondary)); }
        .auth-card { max-width: 440px; border: 0; border-radius: .75rem; box-shadow: 0 1rem 3rem rgba(15, 23, 42, .25); }
        .brand-mark { width: 2.5rem; height: 2.5rem; display: inline-grid; place-items: center; color: #fff; background: var(--aqua-primary); border-radius: .6rem; font-size: 1.3rem; }
        .btn-aqua { background: var(--aqua-primary); border-color: var(--aqua-primary); color: #fff; }
        .btn-aqua:hover { background: #036082; border-color: #036082; color: #fff; }
    </style>
</head>
<body class="d-flex align-items-center py-4">
    <main class="container">
        <div class="card auth-card mx-auto">
            <div class="card-body p-4 p-md-5">
                <a href="{{ route('front.home') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark mb-4">
                    <span class="brand-mark"><i class="bi bi-droplet-half"></i></span>
                    <span class="fs-4 fw-bold">AquaSecure</span>
                </a>
                <h1 class="h4 mb-1">@yield('heading')</h1>
                <p class="text-secondary mb-4">@yield('lead')</p>
                @include('partials.alerts')
                @if (session('status') && ! session('success'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
