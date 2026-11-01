<?php

namespace App\Http\Resources;

use App\Models\Infrastructure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Infrastructure */
class InfrastructureResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => $this->type,
            'localisation' => $this->localisation,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'etat' => $this->etat,
            'date_installation' => $this->date_installation?->toDateString(),
            'description' => $this->description,
            'incidents_count' => $this->whenCounted('incidents'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
