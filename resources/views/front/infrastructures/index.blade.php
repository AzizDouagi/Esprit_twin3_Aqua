@extends('layouts.front')

@section('title', 'Infrastructures')

@section('content')
    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <h1 class="h2 mb-2">Infrastructures publiques</h1>
            <p class="text-secondary mb-0">État général des ouvrages suivis par AquaSecure.</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-3">
                @forelse ($infrastructures as $infrastructure)
                    <div class="col-md-6 col-xl-4">
                        <article class="info-tile">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h2 class="h5 mb-0">{{ $infrastructure->nom }}</h2>
                                <span class="badge text-bg-{{ $infrastructure->statut_badge }}">{{ $infrastructure->etat }}</span>
                            </div>
                            <p class="text-secondary small mb-2">{{ $infrastructure->type }} - {{ $infrastructure->localisation }}</p>
                            <p>{{ str($infrastructure->description)->limit(105) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-secondary">{{ $infrastructure->incidents_count }} incident(s)</span>
                                <a href="{{ route('front.infrastructures.show', $infrastructure) }}" class="btn btn-sm btn-outline-primary">Détail</a>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12"><p class="text-secondary mb-0">Aucune infrastructure disponible.</p></div>
                @endforelse
            </div>
            <div class="mt-4">{{ $infrastructures->links() }}</div>
        </div>
    </section>
@endsection
