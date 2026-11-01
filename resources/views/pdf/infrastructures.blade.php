@extends('pdf.layout')
@section('titre', 'Liste des infrastructures')
@section('contenu')
<table>
    <thead><tr><th>Nom</th><th>Type</th><th>Localisation</th><th>État</th><th>Installation</th><th>Incidents</th></tr></thead>
    <tbody>
    @foreach ($infrastructures as $i)
        <tr><td>{{ $i->nom }}</td><td>{{ $i->type }}</td><td>{{ $i->localisation }}</td><td>{{ $i->etat }}</td><td>{{ $i->date_installation?->format('d/m/Y') }}</td><td>{{ $i->incidents_count }}</td></tr>
    @endforeach
    </tbody>
</table>
@endsection
