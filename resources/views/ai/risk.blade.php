@extends('layouts.admin')
@section('page-title', 'Risque — '.$infrastructure->nom)
@section('content')
<div class="alert alert-info">Score calculé par une heuristique explicable ; ce score ne constitue pas une probabilité de panne. Les données manquantes n’ajoutent aucun point.</div>
<p class="fs-2">{{ $score['score'] }}/100 — {{ $score['niveau'] }}</p>
<table class="table"><thead><tr><th>Facteur</th><th>Points</th><th>Détail</th></tr></thead><tbody>@foreach($score['facteurs'] as $factor)<tr><td>{{ $factor['libelle'] }}</td><td>{{ $factor['points'] }}/{{ $factor['max'] }}</td><td>{{ $factor['detail'] }}</td></tr>@endforeach</tbody></table>
<form method="POST" action="{{ route('ai.risk.explain', $infrastructure) }}">@csrf<button class="btn btn-aqua">Expliquer avec l’IA</button></form>
@if($result) @include('ai.banner') <p class="mt-3">{{ $result['text'] }}</p> @endif
@endsection
