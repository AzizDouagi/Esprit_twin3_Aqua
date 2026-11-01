<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalySuggestion extends Model
{
    protected $fillable = ['mesure_id', 'incident_id', 'reasons', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reasons' => 'array', 'reviewed_at' => 'datetime'];
    }

    public function mesure(): BelongsTo
    {
        return $this->belongsTo(Mesure::class);
    }
}
