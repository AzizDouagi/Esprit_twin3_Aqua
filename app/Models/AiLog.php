<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    protected $fillable = ['user_id', 'feature', 'provider', 'duration_ms', 'status', 'cached'];

    protected function casts(): array
    {
        return ['cached' => 'boolean', 'duration_ms' => 'integer'];
    }
}
