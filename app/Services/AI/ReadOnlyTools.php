<?php

namespace App\Services\AI;

use App\Exceptions\AiException;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Mesure;
use App\Services\RiskScoreService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReadOnlyTools
{
    public function definitions(): array
    {
        $empty = ['type' => 'object', 'properties' => new \stdClass, 'additionalProperties' => false];

        return [
            ['name' => 'network_stats', 'description' => 'Comptages exacts : tous incidents, états, gravités, critiques actifs, infrastructures. Actif = Ouvert ou En cours.', 'input_schema' => $empty],
            ['name' => 'recent_incidents', 'description' => 'Lister les dix derniers incidents et leur nombre total, avec filtres facultatifs par gravité, statut ou infrastructure.', 'input_schema' => ['type' => 'object', 'properties' => ['gravite' => ['type' => 'string', 'enum' => Incident::GRAVITES], 'statut' => ['type' => 'string', 'enum' => Incident::STATUTS], 'actifs' => ['type' => 'boolean', 'description' => 'true pour exclure les incidents résolus'], 'infrastructure_id' => ['type' => 'integer']], 'additionalProperties' => false]],
            ['name' => 'find_infrastructures', 'description' => 'Trouver une infrastructure par nom, localisation ou identifiant. Sans recherche : les vingt premières.', 'input_schema' => ['type' => 'object', 'properties' => ['search' => ['type' => 'string']], 'additionalProperties' => false]],
            ['name' => 'infrastructure_status', 'description' => 'État, dernier relevé et score de risque d’une infrastructure par identifiant.', 'input_schema' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']], 'required' => ['id'], 'additionalProperties' => false]],
            ['name' => 'risk_ranking', 'description' => 'Les cinq infrastructures prioritaires par score de risque, facteurs explicatifs et liens de suivi. Score heuristique, pas une probabilité de panne.', 'input_schema' => $empty],
        ];
    }

    public function run(string $name, array $input): array
    {
        $rules = match ($name) {
            'network_stats','risk_ranking' => [],
            'recent_incidents' => ['gravite' => ['sometimes', 'string', Rule::in(Incident::GRAVITES)], 'statut' => ['sometimes', 'string', Rule::in(Incident::STATUTS)], 'infrastructure_id' => ['sometimes', 'integer', 'min:1'], 'actifs' => ['sometimes', 'boolean']],
            'find_infrastructures' => ['search' => ['sometimes', 'string', 'max:180']],
            'infrastructure_status' => ['id' => ['required', 'integer', 'min:1']],
            default => throw AiException::failed('outil non autorisé.'),
        };
        // Refuser aussi les paramètres inconnus, sans transmettre de SQL ou de texte exécutable.
        if (array_diff(array_keys($input), array_keys($rules)) !== []) {
            throw AiException::failed('paramètre d’outil non autorisé.');
        }
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            throw AiException::failed('arguments d’outil invalides.');
        }
        $data = $validator->validated();

        return match ($name) {
            'network_stats' => $this->stats(),
            'recent_incidents' => $this->incidents($data),
            'find_infrastructures' => ['infrastructures' => Infrastructure::query()->when($data['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('nom', 'like', "%{$search}%")->orWhere('localisation', 'like', "%{$search}%")->when(ctype_digit((string) $search), fn ($q) => $q->orWhere('id', (int) $search))))->orderBy('nom')->limit(20)->get(['id', 'nom', 'localisation', 'etat'])->toArray()],
            'infrastructure_status' => $this->infrastructure((int) $data['id']),
            'risk_ranking' => ['infrastructures' => app(RiskScoreService::class)->classement()->map(fn (Infrastructure $infra) => ['id' => $infra->id, 'nom' => $infra->nom, 'score' => $infra->risque['score'], 'niveau' => $infra->risque['niveau'], 'facteurs' => $infra->risque['facteurs'], 'principal_facteur' => collect($infra->risque['facteurs'])->sortByDesc('points')->first()])->all()],
        };
    }

    public function source(string $name, array $input): array
    {
        return match ($name) {
            'recent_incidents' => ['label' => 'Registre des incidents', 'url' => route('incidents.index', $input)],
            'find_infrastructures' => ['label' => 'Infrastructures', 'url' => route('infrastructures.index')],
            'infrastructure_status' => ['label' => 'Fiche infrastructure #'.$input['id'], 'url' => route('infrastructures.show', $input['id'])],
            'risk_ranking' => ['label' => 'Scores de risque', 'url' => route('dashboard')],
            default => ['label' => 'État du réseau', 'url' => route('dashboard')],
        };
    }

    private function stats(): array
    {
        $statuses = Incident::selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut');

        return [
            'infrastructures' => Infrastructure::count(),
            'incidents_total' => Incident::count(),
            'incidents_ouverts' => (int) ($statuses['Ouvert'] ?? 0),
            'incidents_en_cours' => (int) ($statuses['En cours'] ?? 0),
            'incidents_resolus' => (int) ($statuses['Résolu'] ?? 0),
            'incidents_actifs' => Incident::where('statut', '!=', 'Résolu')->count(),
            'incidents_critiques' => Incident::where('statut', '!=', 'Résolu')->where('gravite', 'Critique')->count(),
            'incidents_critiques_total' => Incident::where('gravite', 'Critique')->count(),
            'incidents_critiques_ouverts' => Incident::where('gravite', 'Critique')->where('statut', 'Ouvert')->count(),
            'incidents_critiques_en_cours' => Incident::where('gravite', 'Critique')->where('statut', 'En cours')->count(),
            'incidents_critiques_resolus' => Incident::where('gravite', 'Critique')->where('statut', 'Résolu')->count(),
            'par_gravite' => Incident::selectRaw('gravite, count(*) as total')->groupBy('gravite')->pluck('total', 'gravite')->toArray(),
        ];
    }

    private function incidents(array $filters): array
    {
        $query = Incident::with('infrastructure')->filtrer($filters)->when($filters['actifs'] ?? false, fn ($q) => $q->where('statut', '!=', 'Résolu'));
        $total = (clone $query)->count();

        return ['total' => $total, 'incidents' => $query->latest('date_incident')->latest('id')->limit(10)->get()->map(fn (Incident $i) => ['id' => $i->id, 'titre' => $i->titre, 'type' => $i->type, 'gravite' => $i->gravite, 'statut' => $i->statut, 'date' => $i->date_incident->format('Y-m-d'), 'infrastructure' => $i->infrastructure?->nom, 'infrastructure_id' => $i->infrastructure_id])->all()];
    }

    private function infrastructure(int $id): array
    {
        $infra = Infrastructure::find($id);
        if (! $infra) {
            return ['message' => 'Infrastructure introuvable ou archivée.'];
        }

        return ['infrastructure' => $infra->only(['id', 'nom', 'localisation', 'etat']), 'incidents_actifs' => $infra->incidents()->where('statut', '!=', 'Résolu')->count(), 'derniere_mesure' => Mesure::where('infrastructure_id', $id)->latest('mesure_le')->first(['debit', 'pression', 'niveau', 'turbidite', 'ph', 'temperature', 'mesure_le'])?->toArray(), 'unites' => array_map(fn (array $grandeur) => $grandeur[1], Mesure::GRANDEURS), 'risque' => $this->risk($infra)];
    }

    private function risk(Infrastructure $infra): array
    {
        $risk = app(RiskScoreService::class)->evaluer($infra);

        return $risk + ['principal_facteur' => collect($risk['facteurs'])->sortByDesc('points')->first()];
    }
}
