<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property list<string> $reasons
 * @property Carbon|null $reviewed_at
 */
class AnomalySuggestion extends Model
{
    protected $fillable = ['mesure_id', 'incident_id', 'reasons', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reasons' => 'array', 'reviewed_at' => 'datetime'];
    }

    /** @return BelongsTo<Mesure, $this> */
    public function mesure(): BelongsTo
    {
        return $this->belongsTo(Mesure::class);
    }
}
