<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Incident::class);

        $search = $request->string('search')->toString();
        $statut = $request->string('statut')->toString();
        $gravite = $request->string('gravite')->toString();

        $incidents = Incident::with('infrastructure')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('titre', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('infrastructure', fn ($query) => $query->where('nom', 'like', "%{$search}%"));
                });
            })
            ->when($statut, fn ($query, string $statut) => $query->where('statut', $statut))
            ->when($gravite, fn ($query, string $gravite) => $query->where('gravite', $gravite))
            ->latest('date_incident')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('incidents.index', [
            'incidents' => $incidents,
            'statuts' => Incident::STATUTS,
            'gravites' => Incident::GRAVITES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Incident::class);

        return view('incidents.create', [
            'types' => Incident::TYPES,
            'gravites' => Incident::GRAVITES,
            'statuts' => Incident::STATUTS,
            'infrastructures' => Infrastructure::orderBy('nom')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $incident = Incident::create($request->validated());

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Incident $incident): View
    {
        $this->authorize('view', $incident);

        $incident->load('infrastructure');

        return view('incidents.show', compact('incident'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Incident $incident): View
    {
        $this->authorize('update', $incident);

        return view('incidents.edit', [
            'incident' => $incident,
            'types' => Incident::TYPES,
            'gravites' => Incident::GRAVITES,
            'statuts' => Incident::STATUTS,
            'infrastructures' => Infrastructure::orderBy('nom')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $incident->update($request->validated());

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incident $incident): RedirectResponse
    {
        $this->authorize('delete', $incident);

        $incident->delete();

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident supprimé avec succès.');
    }
}
