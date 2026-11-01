<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentAssigneNotification extends Notification implements ShouldQueue
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
            ->subject('[AquaSecure] Incident assigné : '.$this->incident->titre)
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Un incident vient de vous être assigné.')
            ->line($this->incident->titre.' ('.$this->incident->gravite.')')
            ->action('Ouvrir l’incident', route('incidents.show', $this->incident));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'incident_assigne',
            'message' => 'Incident assigné : '.$this->incident->titre,
            'url' => route('incidents.show', $this->incident),
        ];
    }
}
