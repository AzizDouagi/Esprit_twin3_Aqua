{{-- Carte Leaflet : $points = [['nom','lat','lng','etat','couleur','url','incidents']] --}}
@once
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush
@endonce
<div id="{{ $mapId ?? 'carte' }}" style="height: {{ $height ?? '420px' }}; border-radius: .5rem; z-index: 0;"></div>
<div class="small text-secondary mt-2">
    <span class="badge" style="background:#198754">Fonctionnelle</span>
    <span class="badge text-dark" style="background:#ffc107">Maintenance</span>
    <span class="badge" style="background:#dc3545">Hors service</span>
</div>
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const points = @json($points);
            const map = L.map(@json($mapId ?? 'carte')).setView([36.82, 10.18], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap', maxZoom: 18,
            }).addTo(map);
            const bounds = [];
            points.forEach(p => {
                const marker = L.circleMarker([p.lat, p.lng], {
                    radius: 10, color: '#fff', weight: 2, fillColor: p.couleur, fillOpacity: .95,
                }).addTo(map);
                marker.bindPopup('<strong>' + p.nom + '</strong><br>' + p.etat + '<br>' + p.incidents + ' incident(s)<br><a href="' + p.url + '">Voir la fiche</a>');
                bounds.push([p.lat, p.lng]);
            });
            if (bounds.length) map.fitBounds(bounds, {padding: [30, 30]});
        })();
    </script>
@endpush
