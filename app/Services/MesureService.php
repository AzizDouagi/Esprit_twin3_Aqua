<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Mesure;
use Carbon\Carbon;

class MesureService
{
    /**
     * Série journalière (moyenne par jour) de chaque grandeur sur N jours.
     *
     * @return array{labels: list<string>, series: array<string, list<float|null>>}
     */
    public function serieJournaliere(Infrastructure $infrastructure, int $jours = 30): array
    {
        $mesures = $infrastructure->mesures()
            ->where('mesure_le', '>=', now()->subDays($jours)->startOfDay())
            ->orderBy('mesure_le')
            ->get();

        $parJour = $mesures->groupBy(fn (Mesure $m) => $m->mesure_le->format('Y-m-d'));

        $labels = [];
        $series = array_fill_keys(array_keys(Mesure::GRANDEURS), []);

        foreach ($parJour as $jour => $lot) {
            $labels[] = Carbon::parse($jour)->format('d/m');
            foreach (array_keys(Mesure::GRANDEURS) as $grandeur) {
                $series[$grandeur][] = round((float) $lot->avg($grandeur), 2);
            }
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Indicateurs de santé : dernière valeur, moyenne 24 h et statut vs seuils.
     *
     * @return list<array{cle: string, libelle: string, unite: string, valeur: float|null, moyenne24h: float|null, statut: string}>
     */
    public function indicateurs(Infrastructure $infrastructure): array
    {
        $derniere = $infrastructure->mesures()->latest('mesure_le')->first();
        $recentes = $infrastructure->mesures()->where('mesure_le', '>=', now()->subDay())->get();
        $indicateurs = [];

        foreach (Mesure::GRANDEURS as $cle => [$libelle, $unite]) {
            $valeur = $derniere?->{$cle};
            [$min, $max] = config("aquasecure.seuils.{$cle}");

            $indicateurs[] = [
                'cle' => $cle,
                'libelle' => $libelle,
                'unite' => $unite,
                'valeur' => $valeur,
                'moyenne24h' => $recentes->isEmpty() ? null : round((float) $recentes->avg($cle), 2),
                'statut' => $valeur === null ? 'secondary' : (($valeur < $min || $valeur > $max) ? 'danger' : 'success'),
            ];
        }

        return $indicateurs;
    }
}
