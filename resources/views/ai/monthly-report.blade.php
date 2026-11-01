@extends('layouts.admin')
@section('page-title', 'Rapport mensuel — '.$infrastructure->nom)
@section('content')
<form class="d-flex gap-2 mb-3" method="GET"><label for="mois">Mois</label><input class="form-control" style="max-width:200px" type="month" id="mois" name="mois" value="{{ $context['mois'] }}"><button class="btn btn-aqua">Afficher</button></form>
<a href="{{ route('ai.report', [$infrastructure, 'mois' => $context['mois'], 'format' => 'pdf']) }}" class="btn btn-outline-primary">Télécharger le PDF</a>
@include('ai.banner')
<p style="white-space:pre-wrap">{{ $result['text'] }}</p>
<p>Incidents : {{ $context['incidents'] }} · Critiques : {{ $context['critiques'] }} · Mesures : {{ $context['mesures'] }}</p>
<table class="table"><thead><tr><th>Incident</th><th>Gravité</th><th>Statut</th></tr></thead><tbody>@forelse($incidents as $incident)<tr><td>{{ $incident->titre }}</td><td>{{ $incident->gravite }}</td><td>{{ $incident->statut }}</td></tr>@empty<tr><td colspan="3">Aucun incident sur cette période.</td></tr>@endforelse</tbody></table>
@endsection
