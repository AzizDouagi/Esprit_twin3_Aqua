<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentCommentaire extends Model
{
    protected $fillable = ['incident_id', 'user_id', 'contenu'];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
