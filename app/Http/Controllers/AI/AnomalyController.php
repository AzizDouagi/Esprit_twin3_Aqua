<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\AnomalyRequest;
use App\Models\AnomalySuggestion;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnomalyController extends Controller
{
    public function index(AnomalyRequest $request): View
    {
        return view('ai.anomalies', ['suggestions' => AnomalySuggestion::with('mesure.infrastructure')->latest('id')->paginate(15)]);
    }

    public function accept(AnomalyRequest $request, AnomalySuggestion $suggestion): RedirectResponse
    {
        $incident = DB::transaction(function () use ($suggestion, $request) {
            $locked = AnomalySuggestion::lockForUpdate()->findOrFail($suggestion->id);
            abort_if($locked->reviewed_at !== null, 409, 'Suggestion déjà traitée.');
            $mesure = $locked->mesure;
            abort_unless($mesure->infrastructure && ! $mesure->infrastructure->trashed(), 422, 'Infrastructure archivée.');
            $incident = Incident::create(['titre' => 'Anomalie du relevé #'.$mesure->id, 'description' => implode('; ', $locked->reasons), 'type' => 'Panne équipement', 'gravite' => 'Moyenne', 'statut' => 'Ouvert', 'date_incident' => $mesure->mesure_le, 'infrastructure_id' => $mesure->infrastructure_id, 'declarant_id' => $request->user()->id]);
            $locked->update(['incident_id' => $incident->id, 'reviewed_at' => now()]);

            return $incident;
        });

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident créé après validation humaine. Ajustez le type et la gravité.');
    }
}
