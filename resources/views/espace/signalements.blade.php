@extends('layouts.front')
@section('title', 'Mes signalements')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Mes signalements</h1>
        <a href="{{ route('espace.declarer') }}" class="btn btn-aqua"><i class="bi bi-plus-circle me-1"></i>Déclarer</a>
    </div>
    @include('espace._liste', ['incidents' => $incidents])
    <div class="mt-3">{{ $incidents->links() }}</div>
</div>
@endsection
