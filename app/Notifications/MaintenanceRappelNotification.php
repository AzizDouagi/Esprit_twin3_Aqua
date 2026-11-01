<?php

namespace App\Notifications;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRappelNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Maintenance $maintenance) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[AquaSecure] Rappel de maintenance : '.$this->maintenance->titre)
            ->line("Maintenance prévue le {$this->maintenance->date_prevue->format('d/m/Y')} sur {$this->maintenance->infrastructure?->nom}.")
            ->action('Voir le planning', route('maintenances.index'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'maintenance_rappel',
            'message' => "Maintenance le {$this->maintenance->date_prevue->format('d/m')} : {$this->maintenance->titre}",
            'url' => route('maintenances.index'),
        ];
    }
}
