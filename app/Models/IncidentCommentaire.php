<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentCommentaire extends Model
{
    protected $fillable = ['incident_id', 'user_id', 'contenu'];

    /** @return BelongsTo<Incident, $this> */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /** @return BelongsTo<User, $this> */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
