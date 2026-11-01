<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeclareIncidentRequest;
use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeclarationController extends Controller
{
    public function index(Request $request): View
    {
        $incidents = Incident::with('infrastructure')
            ->where('declarant_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('espace.signalements', compact('incidents'));
    }

    public function create(): View
    {
        $this->authorize('create', Incident::class);

        return view('espace.declarer', [
            'types' => Incident::TYPES,
            'gravites' => Incident::GRAVITES,
            'infrastructures' => Infrastructure::orderBy('nom')->get(),
        ]);
    }

    public function store(DeclareIncidentRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('photos')->all();

        $incident = Incident::create($data + [
            'statut' => 'Ouvert',
            'date_incident' => today(),
            'declarant_id' => $request->user()->id,
        ]);

        foreach ($request->file('photos', []) as $photo) {
            $incident->piecesJointes()->create([
                'user_id' => $request->user()->id,
                'chemin' => $photo->store("incidents/{$incident->id}", 'local'),
                'nom_original' => $photo->getClientOriginalName(),
                'mime' => $photo->getMimeType(),
                'taille' => $photo->getSize(),
            ]);
        }

        return redirect()->route('espace.signalements.show', $incident)
            ->with('success', 'Merci ! Votre signalement a bien été enregistré.');
    }

    public function show(Request $request, Incident $incident): View
    {
        $this->authorize('view', $incident);

        $incident->load(['infrastructure', 'technicien', 'commentaires.auteur', 'historiques.auteur', 'piecesJointes']);

        return view('espace.signalement', compact('incident'));
    }
}
