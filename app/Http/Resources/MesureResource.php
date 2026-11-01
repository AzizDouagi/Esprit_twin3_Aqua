<?php

namespace App\Http\Resources;

use App\Models\Mesure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Mesure */
class MesureResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'infrastructure_id' => $this->infrastructure_id,
            'debit' => $this->debit,
            'pression' => $this->pression,
            'niveau' => $this->niveau,
            'turbidite' => $this->turbidite,
            'ph' => $this->ph,
            'temperature' => $this->temperature,
            'anomalie' => $this->anomalie,
            'mesure_le' => $this->mesure_le?->toIso8601String(),
        ];
    }
}
