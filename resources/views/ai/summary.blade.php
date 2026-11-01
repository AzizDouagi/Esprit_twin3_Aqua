@extends('layouts.admin')
@section('page-title', 'Résumé de l’incident')
@section('content')
<a href="{{ route('incidents.show', $incident) }}">Retour à {{ $incident->titre }}</a>
@include('ai.banner')
<p class="bg-white border rounded p-4" style="white-space:pre-wrap">{{ $result['text'] }}</p>
@endsection
