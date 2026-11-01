<?php

namespace App\Models;

use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    /** @use HasFactory<MaintenanceFactory> */
    use HasFactory;

    public const STATUTS = ['Planifiée', 'Réalisée', 'Annulée'];

    protected $fillable = [
        'infrastructure_id', 'technicien_id', 'titre', 'notes', 'statut', 'date_prevue', 'date_realisee', 'cout', 'rappel_envoye_le',
    ];

    protected function casts(): array
    {
        return [
            'date_prevue' => 'date',
            'date_realisee' => 'date',
            'rappel_envoye_le' => 'datetime',
            'cout' => 'float',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class)->withTrashed();
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function scopeAVenir(Builder $query, int $jours): Builder
    {
        return $query->where('statut', 'Planifiée')
            ->whereBetween('date_prevue', [today(), today()->addDays($jours)]);
    }

    public function enRetard(): bool
    {
        return $this->statut === 'Planifiée' && $this->date_prevue->isBefore(today());
    }

    public function badge(): string
    {
        return match (true) {
            $this->statut === 'Réalisée' => 'success',
            $this->statut === 'Annulée' => 'secondary',
            $this->enRetard() => 'danger',
            default => 'info',
        };
    }
}
