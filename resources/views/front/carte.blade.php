@extends('layouts.front')
@section('title', 'Carte des infrastructures')
@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="h2 mb-3"><i class="bi bi-geo-alt me-2"></i>Carte des infrastructures</h1>
        <p class="text-secondary">Chaque point est coloré selon l’état de l’infrastructure.</p>
        @include('partials.carte', ['points' => $points, 'height' => '560px'])
    </div>
</section>
@endsection
