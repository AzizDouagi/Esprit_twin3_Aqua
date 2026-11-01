<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\IncidentHistorique;
use App\Models\User;
use App\Notifications\IncidentAssigneNotification;
use App\Notifications\IncidentCritiqueNotification;
use Illuminate\Support\Facades\Notification;

class IncidentObserver
{
    /** Champs tracés dans le journal d'audit. */
    private const CHAMPS_AUDITES = ['statut' => 'Statut', 'gravite' => 'Gravité', 'technicien_id' => 'Technicien', 'type' => 'Type'];

    public function saving(Incident $incident): void
    {
        if ($incident->isDirty('statut')) {
            $incident->resolu_le = $incident->statut === 'Résolu' ? ($incident->resolu_le ?? now()) : null;
        }
    }

    public function created(Incident $incident): void
    {
        $this->journaliser($incident, 'Création', null, "{$incident->statut} / {$incident->gravite}");

        if ($incident->gravite === 'Critique') {
            Notification::send(
                User::permission('incidents.manage')->get(),
                new IncidentCritiqueNotification($incident),
            );
        }

        if ($incident->technicien_id) {
            $incident->technicien?->notify(new IncidentAssigneNotification($incident));
        }
    }

    public function updated(Incident $incident): void
    {
        foreach (self::CHAMPS_AUDITES as $champ => $libelle) {
            if (! $incident->wasChanged($champ)) {
                continue;
            }

            $this->journaliser($incident, $libelle, $this->valeur($champ, $incident->getOriginal($champ)), $this->valeur($champ, $incident->{$champ}));
        }

        if ($incident->wasChanged('technicien_id') && $incident->technicien_id) {
            $incident->technicien?->notify(new IncidentAssigneNotification($incident));
        }

        if ($incident->wasChanged('gravite') && $incident->gravite === 'Critique') {
            Notification::send(
                User::permission('incidents.manage')->get(),
                new IncidentCritiqueNotification($incident),
            );
        }
    }

    private function valeur(string $champ, mixed $valeur): ?string
    {
        if ($champ === 'technicien_id') {
            return $valeur ? (User::query()->whereKey($valeur)->value('name') ?? "#{$valeur}") : 'Non assigné';
        }

        return $valeur === null ? null : (string) $valeur;
    }

    private function journaliser(Incident $incident, string $champ, ?string $avant, ?string $apres): void
    {
        IncidentHistorique::create([
            'incident_id' => $incident->id,
            'user_id' => auth()->id(),
            'champ' => $champ,
            'ancienne_valeur' => $avant,
            'nouvelle_valeur' => $apres,
        ]);
    }
}
