<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Journal d'audit d'un incident : qui a changé quoi, et quand. */
class IncidentHistorique extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['incident_id', 'user_id', 'champ', 'ancienne_valeur', 'nouvelle_valeur'];

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
