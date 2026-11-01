<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\IncidentPieceJointe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncidentPieceJointeController extends Controller
{
    public function store(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('comment', $incident);

        $request->validate([
            'fichiers' => ['required', 'array', 'max:5'],
            'fichiers.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'fichiers.required' => 'Sélectionnez au moins un fichier.',
            'fichiers.*.mimes' => 'Formats acceptés : JPG, PNG, WebP et PDF.',
            'fichiers.*.max' => 'Chaque fichier ne doit pas dépasser 5 Mo.',
        ]);

        foreach ($request->file('fichiers') as $fichier) {
            $incident->piecesJointes()->create([
                'user_id' => $request->user()->id,
                'chemin' => $fichier->store("incidents/{$incident->id}", 'local'),
                'nom_original' => $fichier->getClientOriginalName(),
                'mime' => $fichier->getMimeType(),
                'taille' => $fichier->getSize(),
            ]);
        }

        return back()->with('success', 'Pièce(s) jointe(s) ajoutée(s).');
    }

    public function show(Incident $incident, IncidentPieceJointe $pieceJointe): StreamedResponse
    {
        $this->authorize('view', $incident);
        abort_unless($pieceJointe->incident_id === $incident->id, 404);

        return Storage::disk('local')->response($pieceJointe->chemin, $pieceJointe->nom_original);
    }

    public function destroy(Incident $incident, IncidentPieceJointe $pieceJointe): RedirectResponse
    {
        $this->authorize('update', $incident);
        abort_unless($pieceJointe->incident_id === $incident->id, 404);

        Storage::disk('local')->delete($pieceJointe->chemin);
        $pieceJointe->delete();

        return back()->with('success', 'Pièce jointe supprimée.');
    }
}
