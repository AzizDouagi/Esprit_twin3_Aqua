<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentFilterRequest;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\User;
use App\Rules\ActiveTechnician;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public const FILTRES = ['search', 'statut', 'gravite', 'infrastructure_id', 'technicien_id', 'du', 'au'];

    public function index(IncidentFilterRequest $request): View
    {
        $this->authorize('viewAny', Incident::class);

        $incidents = Incident::with(['infrastructure', 'technicien'])
            ->filtrer($request->only(self::FILTRES))
            ->trier($request->string('tri')->toString(), $request->string('sens')->toString())
            ->paginate(10)
            ->withQueryString();

        return view('incidents.index', [
            'incidents' => $incidents,
            'statuts' => Incident::STATUTS,
            'gravites' => Incident::GRAVITES,
            'infrastructures' => Infrastructure::orderBy('nom')->get(),
            'techniciens' => $this->techniciens(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Incident::class);

        return view('incidents.create', $this->formData());
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $incident = Incident::create($request->validated() + ['declarant_id' => $request->user()->id]);

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident ajouté avec succès.');
    }

    public function show(Incident $incident): View
    {
        $this->authorize('view', $incident);

        $incident->load(['infrastructure', 'declarant', 'technicien', 'commentaires.auteur', 'historiques.auteur', 'piecesJointes']);

        return view('incidents.show', [
            'incident' => $incident,
            'techniciens' => $this->techniciens(),
        ]);
    }

    public function edit(Incident $incident): View
    {
        $this->authorize('update', $incident);

        return view('incidents.edit', ['incident' => $incident] + $this->formData());
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $incident->update($request->validated());

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident modifié avec succès.');
    }

    /** Workflow : Ouvert → En cours → Résolu (transitions contrôlées, journalisées). */
    public function changerStatut(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('update', $incident);

        $data = $request->validate(['statut' => ['required', Rule::in(Incident::STATUTS)]]);

        if (! in_array($data['statut'], $incident->transitionsPossibles(), true)) {
            return back()->withErrors(['statut' => "Transition « {$incident->statut} → {$data['statut']} » non autorisée."]);
        }

        $incident->update($data);

        return back()->with('success', "Statut passé à « {$data['statut']} ».");
    }

    public function assigner(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('update', $incident);

        $data = $request->validate([
            'technicien_id' => ['nullable', 'integer', new ActiveTechnician],
        ]);

        $incident->update(['technicien_id' => $data['technicien_id'] ?? null]);

        return back()->with('success', 'Assignation mise à jour.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $this->authorize('delete', $incident);

        $incident->delete();

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident supprimé avec succès.');
    }

    /** @return Collection<int, User> */
    private function techniciens()
    {
        return User::role([User::ROLE_TECHNICIEN, User::ROLE_ADMIN])->where('actif', true)->orderBy('name')->get();
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'types' => Incident::TYPES,
            'gravites' => Incident::GRAVITES,
            'statuts' => Incident::STATUTS,
            'infrastructures' => Infrastructure::orderBy('nom')->get(),
            'techniciens' => $this->techniciens(),
        ];
    }
}
