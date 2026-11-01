@extends('pdf.layout')
@section('titre', 'Liste des incidents')
@section('contenu')
<table>
    <thead><tr><th>Date</th><th>Titre</th><th>Infrastructure</th><th>Type</th><th>Gravité</th><th>Statut</th><th>Technicien</th></tr></thead>
    <tbody>
    @foreach ($incidents as $i)
        <tr><td>{{ $i->date_incident->format('d/m/Y') }}</td><td>{{ $i->titre }}</td><td>{{ $i->infrastructure?->nom }}</td><td>{{ $i->type }}</td><td>{{ $i->gravite }}</td><td>{{ $i->statut }}</td><td>{{ $i->technicien?->name ?? '—' }}</td></tr>
    @endforeach
    </tbody>
</table>
@endsection
