<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentPieceJointe extends Model
{
    protected $table = 'incident_pieces_jointes';

    protected $fillable = ['incident_id', 'user_id', 'chemin', 'nom_original', 'mime', 'taille'];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
