<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\Support\Collection;

/** Indicateurs du tableau de bord (calculés en PHP pour rester portables SQLite/MySQL). */
class DashboardStats
{
    /** MTTR : durée moyenne (heures) entre la création et la résolution d'un incident. */
    public function mttrHeures(): ?float
    {
        $resolus = Incident::whereNotNull('resolu_le')->get(['created_at', 'resolu_le']);

        if ($resolus->isEmpty()) {
            return null;
        }

        $moyenneMinutes = $resolus->avg(fn (Incident $i) => max(0, $i->created_at->diffInMinutes($i->resolu_le, true)));

        return round($moyenneMinutes / 60, 1);
    }

    /** @return array<string, int> */
    public function parGravite(): array
    {
        $counts = Incident::selectRaw('gravite, count(*) as total')->groupBy('gravite')->pluck('total', 'gravite');

        return collect(Incident::GRAVITES)->mapWithKeys(fn (string $g) => [$g => (int) ($counts[$g] ?? 0)])->all();
    }

    /** Incidents des 6 derniers mois. @return array<string, int> */
    public function parMois(int $mois = 6): array
    {
        $debut = now()->startOfMonth()->subMonths($mois - 1);
        $dates = Incident::where('date_incident', '>=', $debut)->pluck('date_incident');

        $serie = [];
        for ($i = 0; $i < $mois; $i++) {
            $serie[$debut->copy()->addMonths($i)->format('Y-m')] = 0;
        }
        foreach ($dates as $date) {
            $cle = $date->format('Y-m');
            if (isset($serie[$cle])) {
                $serie[$cle]++;
            }
        }

        return $serie;
    }

    /** Infrastructures les plus exposées (score pondéré par la gravité des incidents ouverts/récents). @return Collection<int, Infrastructure> */
    public function topRisque(int $limite = 5): Collection
    {
        return app(RiskScoreService::class)->classement($limite);
    }
}
