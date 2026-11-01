<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - AquaSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #075985, #0891b2); color: #fff; }
        .code { font-size: 6rem; font-weight: 800; line-height: 1; }
    </style>
</head>
<body class="d-flex align-items-center text-center">
    <main class="container">
        <i class="bi bi-@yield('icon', 'droplet-half') display-4"></i>
        <div class="code">@yield('code')</div>
        <h1 class="h3 mt-2">@yield('title')</h1>
        <p class="opacity-75">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn btn-light mt-2"><i class="bi bi-house me-1"></i>Retour à l’accueil</a>
    </main>
</body>
</html>
