<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentCritiqueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Incident $incident) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('[AquaSecure] Incident critique : '.$this->incident->titre)
            ->greeting('Incident critique détecté')
            ->line("Infrastructure : {$this->incident->infrastructure?->nom}")
            ->line($this->incident->titre)
            ->action('Ouvrir l’incident', route('incidents.show', $this->incident));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'incident_critique',
            'message' => 'Incident critique : '.$this->incident->titre,
            'url' => route('incidents.show', $this->incident),
        ];
    }
}
