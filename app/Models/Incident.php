<?php

namespace App\Models;

use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory;

    public const TYPES = [
        'Fuite',
        'Contamination',
        'Coupure',
        'Pression anormale',
        'Panne équipement',
    ];

    public const GRAVITES = [
        'Faible',
        'Moyenne',
        'Élevée',
        'Critique',
    ];

    public const STATUTS = [
        'Ouvert',
        'En cours',
        'Résolu',
    ];

    protected $fillable = [
        'titre',
        'description',
        'type',
        'gravite',
        'statut',
        'date_incident',
        'infrastructure_id',
    ];

    protected function casts(): array
    {
        return [
            'date_incident' => 'date',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }

    public function graviteBadge(): string
    {
        return match ($this->gravite) {
            'Faible' => 'success',
            'Moyenne' => 'info',
            'Élevée' => 'warning',
            'Critique' => 'danger',
            default => 'secondary',
        };
    }

    public function statutBadge(): string
    {
        return match ($this->statut) {
            'Ouvert' => 'danger',
            'En cours' => 'warning',
            'Résolu' => 'success',
            default => 'secondary',
        };
    }
}
