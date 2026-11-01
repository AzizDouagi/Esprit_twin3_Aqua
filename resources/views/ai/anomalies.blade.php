@extends('layouts.admin')
@section('page-title', 'Anomalies et incidents suggérés')
@section('content')
<div class="alert alert-info">Suggestions issues de l’analyse statistique locale. Validation humaine requise. Le type et la gravité restent modifiables après création.</div>
<p>Analyse planifiée chaque heure. Commande manuelle : <code>php artisan mesures:detect-anomalies</code>.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Infrastructure / relevé</th><th>Motifs</th><th>Validation</th></tr></thead><tbody>
@forelse($suggestions as $suggestion)
<tr><td>{{ $suggestion->mesure->infrastructure?->nom }}<br>#{{ $suggestion->mesure_id }} — {{ $suggestion->mesure->mesure_le->format('d/m/Y H:i') }}</td><td>{{ implode('; ', $suggestion->reasons) }}</td><td>
@if($suggestion->reviewed_at)Validé @if($suggestion->incident_id)<a href="{{ route('incidents.show', $suggestion->incident_id) }}">Incident #{{ $suggestion->incident_id }}</a>@endif
@else<form method="POST" action="{{ route('ai.anomalies.accept', $suggestion) }}">@csrf<label class="small"><input type="checkbox" name="confirmer" value="1" required> Je confirme cette anomalie</label><button class="btn btn-sm btn-outline-primary">Créer l’incident</button></form>@endif
</td></tr>
@empty<tr><td colspan="3">Aucune suggestion. Le détecteur peut être exécuté depuis la console.</td></tr>@endforelse
</tbody></table></div>{{ $suggestions->links() }}
@endsection
