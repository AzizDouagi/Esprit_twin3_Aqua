<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInfrastructureRequest;
use App\Http\Requests\UpdateInfrastructureRequest;
use App\Models\Infrastructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfrastructureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Infrastructure::class);

        $search = $request->string('search')->toString();
        $etat = $request->string('etat')->toString();

        $infrastructures = Infrastructure::withCount('incidents')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nom', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('localisation', 'like', "%{$search}%");
                });
            })
            ->when($etat, fn ($query, string $etat) => $query->where('etat', $etat))
            ->orderBy('nom')
            ->paginate(8)
            ->withQueryString();

        return view('infrastructures.index', [
            'infrastructures' => $infrastructures,
            'etats' => Infrastructure::ETATS,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Infrastructure::class);

        return view('infrastructures.create', [
            'types' => Infrastructure::TYPES,
            'etats' => Infrastructure::ETATS,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInfrastructureRequest $request): RedirectResponse
    {
        $infrastructure = Infrastructure::create($request->validated());

        return redirect()
            ->route('infrastructures.show', $infrastructure)
            ->with('success', 'Infrastructure ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Infrastructure $infrastructure): View
    {
        $this->authorize('view', $infrastructure);

        $infrastructure->load([
            'incidents' => fn ($query) => $query->latest('date_incident')->latest(),
        ]);

        return view('infrastructures.show', compact('infrastructure'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Infrastructure $infrastructure): View
    {
        $this->authorize('update', $infrastructure);

        return view('infrastructures.edit', [
            'infrastructure' => $infrastructure,
            'types' => Infrastructure::TYPES,
            'etats' => Infrastructure::ETATS,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInfrastructureRequest $request, Infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->update($request->validated());

        return redirect()
            ->route('infrastructures.show', $infrastructure)
            ->with('success', 'Infrastructure modifiée avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Infrastructure $infrastructure): RedirectResponse
    {
        $this->authorize('delete', $infrastructure);

        $infrastructure->delete();

        return redirect()
            ->route('infrastructures.index')
            ->with('success', 'Infrastructure supprimée avec ses incidents associés.');
    }
}
