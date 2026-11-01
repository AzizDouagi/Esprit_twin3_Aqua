<?php

namespace App\Models;

use App\Observers\IncidentObserver;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $date_incident
 * @property Carbon|null $resolu_le
 */
#[ObservedBy(IncidentObserver::class)]
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

    /** Transitions de statut autorisées (workflow). */
    public const TRANSITIONS = [
        'Ouvert' => ['En cours'],
        'En cours' => ['Résolu', 'Ouvert'],
        'Résolu' => ['En cours'],
    ];

    protected $fillable = [
        'titre',
        'description',
        'type',
        'gravite',
        'statut',
        'date_incident',
        'infrastructure_id',
        'declarant_id',
        'technicien_id',
        'resolu_le',
    ];

    protected function casts(): array
    {
        return [
            'date_incident' => 'date',
            'resolu_le' => 'datetime',
        ];
    }

    /** @return BelongsTo<Infrastructure, $this> */
    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class)->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function declarant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declarant_id');
    }

    /** @return BelongsTo<User, $this> */
    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    /** @return HasMany<IncidentCommentaire, $this> */
    public function commentaires(): HasMany
    {
        return $this->hasMany(IncidentCommentaire::class)->latest();
    }

    /** @return HasMany<IncidentHistorique, $this> */
    public function historiques(): HasMany
    {
        return $this->hasMany(IncidentHistorique::class)->latest('id');
    }

    /** @return HasMany<IncidentPieceJointe, $this> */
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(IncidentPieceJointe::class);
    }

    public function transitionsPossibles(): array
    {
        return self::TRANSITIONS[$this->statut] ?? [];
    }

    /**
     * Filtres avancés : période, gravité, statut, infrastructure, technicien, recherche.
     *
     * @param  array<string, mixed>  $f
     */
    public function scopeFiltrer(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('titre', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%")
                ->orWhere('type', 'like', "%{$s}%")
                ->orWhereHas('infrastructure', fn ($q) => $q->where('nom', 'like', "%{$s}%"))))
            ->when($f['statut'] ?? null, fn ($q, $v) => $q->where('statut', $v))
            ->when($f['gravite'] ?? null, fn ($q, $v) => $q->where('gravite', $v))
            ->when($f['infrastructure_id'] ?? null, fn ($q, $v) => $q->where('infrastructure_id', $v))
            ->when($f['technicien_id'] ?? null, fn ($q, $v) => $v === 'none' ? $q->whereNull('technicien_id') : $q->where('technicien_id', $v))
            ->when($f['du'] ?? null, fn ($q, $v) => $q->whereDate('date_incident', '>=', $v))
            ->when($f['au'] ?? null, fn ($q, $v) => $q->whereDate('date_incident', '<=', $v));
    }

    public function scopeTrier(Builder $query, ?string $tri, ?string $sens): Builder
    {
        $colonne = in_array($tri, ['date_incident', 'gravite', 'statut', 'titre', 'created_at'], true) ? $tri : 'date_incident';

        if ($colonne === 'gravite') {
            $ordre = $sens === 'asc' ? 'ASC' : 'DESC';

            return $query->orderByRaw("CASE gravite WHEN 'Critique' THEN 4 WHEN 'Élevée' THEN 3 WHEN 'Moyenne' THEN 2 ELSE 1 END {$ordre}")->orderByDesc('id');
        }

        return $query->orderBy($colonne, $sens === 'asc' ? 'asc' : 'desc')->orderByDesc('id');
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
