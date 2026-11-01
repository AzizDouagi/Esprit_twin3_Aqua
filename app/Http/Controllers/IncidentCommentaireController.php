<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IncidentCommentaireController extends Controller
{
    public function store(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('comment', $incident);

        $data = $request->validate(
            ['contenu' => ['required', 'string', 'max:2000']],
            ['contenu.required' => 'Le commentaire ne peut pas être vide.'],
        );

        $incident->commentaires()->create($data + ['user_id' => $request->user()->id]);

        return back()->with('success', 'Commentaire ajouté.');
    }
}
