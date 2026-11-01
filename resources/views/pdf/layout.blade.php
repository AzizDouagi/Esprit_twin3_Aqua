<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
    h1 { color: #075985; font-size: 20px; margin: 0 0 4px; }
    h2 { color: #075985; font-size: 14px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; margin-top: 18px; }
    .meta { color: #64748b; margin-bottom: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    th { background: #075985; color: #fff; text-align: left; padding: 5px; font-size: 10px; }
    td { padding: 4px 5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .badge { padding: 1px 6px; border-radius: 8px; color: #fff; font-size: 9px; }
    .ia { background: #eef2ff; border: 1px solid #c7d2fe; padding: 8px; margin-top: 10px; }
    footer { position: fixed; bottom: -10px; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>
    <h1>@yield('titre')</h1>
    <div class="meta">AquaSecure — généré le {{ now()->format('d/m/Y H:i') }}</div>
    @yield('contenu')
    <footer>AquaSecure — supervision des infrastructures hydrauliques</footer>
</body>
</html>
