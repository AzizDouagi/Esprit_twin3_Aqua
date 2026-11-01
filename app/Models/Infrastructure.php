<?php

namespace App\Models;

use Database\Factories\InfrastructureFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $date_installation
 * @property array{score: int, niveau: string, couleur: string, facteurs: array} $risque
 */
class Infrastructure extends Model
{
    /** @use HasFactory<InfrastructureFactory> */
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'Station de pompage',
        'Réservoir',
        'Canalisation',
        'Station de traitement',
        'Château d’eau',
    ];

    public const ETATS = [
        'Fonctionnelle',
        'Maintenance',
        'Hors service',
    ];

    protected $fillable = [
        'nom',
        'type',
        'localisation',
        'latitude',
        'longitude',
        'etat',
        'date_installation',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date_installation' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /** @return HasMany<Incident, $this> */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /** @return HasMany<Mesure, $this> */
    public function mesures(): HasMany
    {
        return $this->hasMany(Mesure::class);
    }

    /** @return HasMany<Maintenance, $this> */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function couleurEtat(): string
    {
        return match ($this->etat) {
            'Fonctionnelle' => '#198754',
            'Maintenance' => '#ffc107',
            'Hors service' => '#dc3545',
            default => '#6c757d',
        };
    }

    protected function statutBadge(): Attribute
    {
        return Attribute::get(fn (): string => match ($this->etat) {
            'Fonctionnelle' => 'success',
            'Maintenance' => 'warning',
            'Hors service' => 'danger',
            default => 'secondary',
        });
    }
}
