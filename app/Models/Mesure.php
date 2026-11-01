<?php

namespace App\Models;

use Database\Factories\MesureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $mesure_le
 */
class Mesure extends Model
{
    /** @use HasFactory<MesureFactory> */
    use HasFactory;

    /** Grandeurs mesurées et leur libellé / unité. */
    public const GRANDEURS = [
        'debit' => ['Débit', 'm³/h'],
        'pression' => ['Pression', 'bar'],
        'niveau' => ['Niveau', '%'],
        'turbidite' => ['Turbidité', 'NTU'],
        'ph' => ['pH', ''],
        'temperature' => ['Température', '°C'],
    ];

    protected $fillable = [
        'infrastructure_id', 'debit', 'pression', 'niveau', 'turbidite', 'ph', 'temperature', 'anomalie', 'mesure_le',
    ];

    protected function casts(): array
    {
        return [
            'mesure_le' => 'datetime',
            'anomalie' => 'boolean',
            'debit' => 'float',
            'pression' => 'float',
            'niveau' => 'float',
            'turbidite' => 'float',
            'ph' => 'float',
            'temperature' => 'float',
        ];
    }

    /** @return BelongsTo<Infrastructure, $this> */
    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class)->withTrashed();
    }
}
