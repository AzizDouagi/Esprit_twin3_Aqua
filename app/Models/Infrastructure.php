<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Infrastructure extends Model
{
    /** @use HasFactory<\Database\Factories\InfrastructureFactory> */
    use HasFactory;

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
        'etat',
        'date_installation',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date_installation' => 'date',
        ];
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
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
