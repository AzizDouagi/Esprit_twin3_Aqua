<?php

namespace App\Console\Commands;

use App\Models\Maintenance;
use App\Models\User;
use App\Notifications\MaintenanceRappelNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class MaintenancesRappelsCommand extends Command
{
    protected $signature = 'maintenances:rappels {--jours= : Fenêtre de rappel en jours}';

    protected $description = 'Envoie un rappel (mail + notification) pour les maintenances planifiées proches.';

    public function handle(): int
    {
        $jours = (int) ($this->option('jours') ?: config('aquasecure.maintenance.rappel_jours'));
        $envoyes = 0;

        Maintenance::with(['infrastructure', 'technicien'])
            ->aVenir($jours)
            ->whereNull('rappel_envoye_le')
            ->each(function (Maintenance $maintenance) use (&$envoyes): void {
                $destinataires = $maintenance->technicien
                    ? collect([$maintenance->technicien])
                    : User::permission('maintenances.manage')->get();

                Notification::send($destinataires, new MaintenanceRappelNotification($maintenance));
                $maintenance->update(['rappel_envoye_le' => now()]);
                $envoyes++;
            });

        $this->info("{$envoyes} rappel(s) de maintenance envoyé(s).");

        return self::SUCCESS;
    }
}
