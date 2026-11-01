@extends('pdf.layout')
@section('titre', 'Fiche : '.$infrastructure->nom)
@section('contenu')
<table>
    <tr><th style="width:25%">Type</th><td>{{ $infrastructure->type }}</td></tr>
    <tr><th>Localisation</th><td>{{ $infrastructure->localisation }} @if($infrastructure->latitude) ({{ $infrastructure->latitude }}, {{ $infrastructure->longitude }}) @endif</td></tr>
    <tr><th>État</th><td>{{ $infrastructure->etat }}</td></tr>
    <tr><th>Installation</th><td>{{ $infrastructure->date_installation?->format('d/m/Y') ?? 'n/c' }}</td></tr>
    <tr><th>Description</th><td>{{ $infrastructure->description }}</td></tr>
</table>
<h2>Indicateurs (dernier relevé)</h2>
<table>
    <thead><tr><th>Grandeur</th><th>Valeur</th><th>Moyenne 24 h</th><th>Statut</th></tr></thead>
    <tbody>
    @foreach ($indicateurs as $ind)
        <tr><td>{{ $ind['libelle'] }}</td><td>{{ $ind['valeur'] ?? '—' }} {{ $ind['unite'] }}</td><td>{{ $ind['moyenne24h'] ?? '—' }}</td><td>{{ $ind['statut'] === 'danger' ? 'Hors seuil' : ($ind['statut'] === 'success' ? 'Normal' : 'n/c') }}</td></tr>
    @endforeach
    </tbody>
</table>
<h2>Incidents ({{ $infrastructure->incidents->count() }})</h2>
<table>
    <thead><tr><th>Date</th><th>Titre</th><th>Type</th><th>Gravité</th><th>Statut</th></tr></thead>
    <tbody>
    @forelse ($infrastructure->incidents as $inc)
        <tr><td>{{ $inc->date_incident->format('d/m/Y') }}</td><td>{{ $inc->titre }}</td><td>{{ $inc->type }}</td><td>{{ $inc->gravite }}</td><td>{{ $inc->statut }}</td></tr>
    @empty
        <tr><td colspan="5">Aucun incident.</td></tr>
    @endforelse
    </tbody>
</table>
<h2>Maintenances</h2>
<table>
    <thead><tr><th>Date prévue</th><th>Intervention</th><th>Statut</th><th>Coût</th></tr></thead>
    <tbody>
    @forelse ($infrastructure->maintenances as $m)
        <tr><td>{{ $m->date_prevue->format('d/m/Y') }}</td><td>{{ $m->titre }}</td><td>{{ $m->statut }}</td><td>{{ $m->cout ? number_format($m->cout, 2, ',', ' ').' DT' : '—' }}</td></tr>
    @empty
        <tr><td colspan="4">Aucune maintenance.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
