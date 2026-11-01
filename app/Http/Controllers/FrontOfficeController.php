<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\View\View;

class FrontOfficeController extends Controller
{
    public function home(): View
    {
        return view('front.home', [
            'totalInfrastructures' => Infrastructure::count(),
            'totalIncidentsOuverts' => Incident::where('statut', 'Ouvert')->count(),
            'incidentsRecents' => Incident::with('infrastructure')
                ->whereIn('statut', ['Ouvert', 'En cours'])
                ->latest('date_incident')
                ->take(4)
                ->get(),
        ]);
    }

    public function carte(): View
    {
        return view('front.carte', ['points' => $this->pointsCarte(false)]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pointsCarte(bool $admin = false): array
    {
        return Infrastructure::withCount('incidents')
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->get()
            ->map(fn (Infrastructure $i) => [
                'nom' => $i->nom,
                'lat' => $i->latitude,
                'lng' => $i->longitude,
                'etat' => $i->etat,
                'couleur' => $i->couleurEtat(),
                'incidents' => $i->incidents_count,
                'url' => $admin ? route('infrastructures.show', $i) : route('front.infrastructures.show', $i),
            ])->all();
    }

    public function infrastructures(): View
    {
        $infrastructures = Infrastructure::withCount('incidents')
            ->orderBy('nom')
            ->paginate(9);

        return view('front.infrastructures.index', compact('infrastructures'));
    }

    public function infrastructureShow(Infrastructure $infrastructure): View
    {
        $infrastructure->load([
            'incidents' => fn ($query) => $query->latest('date_incident')->take(8),
        ])->loadCount('incidents');

        return view('front.infrastructures.show', compact('infrastructure'));
    }
}
