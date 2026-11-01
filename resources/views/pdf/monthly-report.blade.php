@extends('pdf.layout')
@section('titre', 'Rapport mensuel — '.$infrastructure->nom)
@section('contenu')
<p>Période : {{ $context['mois'] }}</p>
<div class="ia">Généré par IA — {{ $result['provider'] }}{{ $result['degraded'] ? ' — mode dégradé' : '' }}. À vérifier par un responsable.</div>
<p>{{ $result['text'] }}</p>
<p>Incidents : {{ $context['incidents'] }} ; critiques : {{ $context['critiques'] }} ; mesures : {{ $context['mesures'] }}.</p>
<table><thead><tr><th>Incident</th><th>Gravité</th><th>Statut</th></tr></thead><tbody>@foreach($incidents as $incident)<tr><td>{{ $incident->titre }}</td><td>{{ $incident->gravite }}</td><td>{{ $incident->statut }}</td></tr>@endforeach</tbody></table>
@endsection
