<?php

namespace App\Http\Resources;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Incident */
class IncidentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'type' => $this->type,
            'gravite' => $this->gravite,
            'statut' => $this->statut,
            'date_incident' => $this->date_incident?->toDateString(),
            'resolu_le' => $this->resolu_le?->toIso8601String(),
            'infrastructure' => new InfrastructureResource($this->whenLoaded('infrastructure')),
            'technicien' => $this->whenLoaded('technicien', fn () => $this->technicien ? ['id' => $this->technicien->id, 'name' => $this->technicien->name] : null),
            'declarant_id' => $this->declarant_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
