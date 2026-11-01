<?php

namespace App\Services;

use App\Models\AnomalySuggestion;
use App\Models\Infrastructure;
use App\Models\Mesure;

class AnomalyDetector
{
    /** Compare uniquement aux relevés antérieurs ; le point évalué est exclu. */
    public function reasons(Mesure $mesure): array
    {
        $cfg = config('aquasecure.anomalies');
        $history = Mesure::where('infrastructure_id', $mesure->infrastructure_id)->where('mesure_le', '<', $mesure->mesure_le)->where('mesure_le', '>=', $mesure->mesure_le->copy()->subDays($cfg['fenetre_jours']))->latest('mesure_le')->limit($cfg['methode'] === 'moyenne_mobile' ? $cfg['fenetre_mobile'] : 1000)->get();
        $reasons = [];
        foreach (config('aquasecure.seuils') as $field => [$min,$max]) {
            $value = $mesure->{$field};
            if ($value === null) {
                continue;
            }
            if ($value < $min || $value > $max) {
                $reasons[] = "$field : $value hors seuil [$min, $max]";

                continue;
            }
            $values = $history->pluck($field)->filter(fn ($v) => $v !== null)->values();
            $minimum = $cfg['methode'] === 'moyenne_mobile' ? $cfg['fenetre_mobile'] : $cfg['min_points'];
            if ($values->count() < $minimum) {
                continue;
            }
            $mean = $values->avg();
            if ($cfg['methode'] === 'moyenne_mobile') {
                if (abs($value - $mean) / max(abs($mean), 0.001) > $cfg['ecart_mobile']) {
                    $reasons[] = "$field : écart à la moyenne mobile";
                }
            } else {
                $std = sqrt($values->sum(fn ($v) => ($v - $mean) ** 2) / $values->count());
                if (($std > 0 && abs($value - $mean) / $std >= $cfg['seuil_z']) || ($std == 0 && abs($value - $mean) > 0.001)) {
                    $reasons[] = "$field : rupture statistique (z-score)";
                }
            }
        }

        return $reasons;
    }

    public function scan(): int
    {
        $count = 0;
        $ids = Infrastructure::pluck('id');
        foreach (Mesure::whereIn('infrastructure_id', $ids)->where('mesure_le', '>=', now()->subDays(config('aquasecure.anomalies.fenetre_jours')))->orderBy('id')->lazyById(100) as $mesure) {
            $reasons = $this->reasons($mesure);
            $mesure->update(['anomalie' => count($reasons) > 0]);
            if ($reasons !== []) {
                $suggestion = AnomalySuggestion::firstOrCreate(['mesure_id' => $mesure->id], ['reasons' => $reasons]);
                if ($suggestion->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
