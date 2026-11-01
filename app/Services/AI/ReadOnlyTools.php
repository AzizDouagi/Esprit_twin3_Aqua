<?php

namespace App\Services\AI;

use App\Exceptions\AiException;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Mesure;
use Illuminate\Support\Facades\Validator;

class ReadOnlyTools
{
    public function definitions(): array
    {
        return [
            ['name' => 'network_stats', 'description' => 'Nombre d’infrastructures, incidents ouverts et critiques.', 'input_schema' => ['type' => 'object', 'properties' => new \stdClass, 'additionalProperties' => false]],
            ['name' => 'recent_incidents', 'description' => 'Les dix derniers incidents (type, gravité, statut uniquement).', 'input_schema' => ['type' => 'object', 'properties' => new \stdClass, 'additionalProperties' => false]],
            ['name' => 'infrastructure_status', 'description' => 'État et dernier relevé d’une infrastructure par identifiant.', 'input_schema' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']], 'required' => ['id'], 'additionalProperties' => false]],
        ];
    }

    public function run(string $name, array $input): array
    {
        if (! in_array($name, ['network_stats', 'recent_incidents', 'infrastructure_status'], true)) {
            throw AiException::failed('outil non autorisé.');
        }
        Validator::make($input, $name === 'infrastructure_status' ? ['id' => ['required', 'integer', 'min:1']] : [])->validate();

        return match ($name) {
            'network_stats' => ['infrastructures' => Infrastructure::count(), 'incidents_ouverts' => Incident::where('statut', '!=', 'Résolu')->count(), 'incidents_critiques' => Incident::where('statut', '!=', 'Résolu')->where('gravite', 'Critique')->count()],
            'recent_incidents' => Incident::latest('id')->limit(10)->get(['id', 'type', 'gravite', 'statut', 'infrastructure_id'])->toArray(),
            'infrastructure_status' => ['infrastructure' => Infrastructure::find($input['id'], ['id', 'nom', 'etat'])?->toArray(), 'derniere_mesure' => Infrastructure::whereKey($input['id'])->exists() ? Mesure::where('infrastructure_id', $input['id'])->latest('mesure_le')->first(['debit', 'pression', 'niveau', 'turbidite', 'ph', 'temperature', 'mesure_le'])?->toArray() : null],
        };
    }
}
