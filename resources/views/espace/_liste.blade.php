<div class="bg-white border rounded-2">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Date</th><th>Titre</th><th>Infrastructure</th><th>Gravité</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            @forelse ($incidents as $i)
                <tr>
                    <td>{{ $i->date_incident->format('d/m/Y') }}</td>
                    <td>{{ $i->titre }}</td>
                    <td>{{ $i->infrastructure?->nom }}</td>
                    <td><span class="badge text-bg-{{ $i->graviteBadge() }}">{{ $i->gravite }}</span></td>
                    <td><span class="badge text-bg-{{ $i->statutBadge() }}">{{ $i->statut }}</span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('espace.signalements.show', $i) }}"><i class="bi bi-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">Vous n’avez encore déclaré aucun incident.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
