<?php

namespace App\Services;

use App\Models\Infrastructure;
use Illuminate\Support\Collection;

/**
 * Score de risque 0-100 d'une infrastructure (modèle heuristique pondéré, documenté dans docs/IA.md).
 *
 * Facteurs (points max) : relevés hors seuil sur 7 j (30) · incidents pondérés sur 90 j (30)
 * · état opérationnel (15) · maintenances en retard (15) · âge (10).
 */
class RiskScoreService
{
    private const POIDS_GRAVITE = ['Faible' => 1, 'Moyenne' => 2, 'Élevée' => 4, 'Critique' => 8];

    private const POINTS_ETAT = ['Fonctionnelle' => 0, 'Maintenance' => 8, 'Hors service' => 15];

    /**
     * @return array{score: int, niveau: string, couleur: string, facteurs: array<int, array{libelle: string, points: float, max: int, detail: string}>}
     */
    public function evaluer(Infrastructure $infra): array
    {
        $seuils = config('aquasecure.seuils');

        $mesures = $infra->mesures()->where('mesure_le', '>=', now()->subDays(7))->get();
        $horsSeuil = $mesures->filter(function ($m) use ($seuils): bool {
            foreach ($seuils as $grandeur => [$min, $max]) {
                if ($m->{$grandeur} !== null && ($m->{$grandeur} < $min || $m->{$grandeur} > $max)) {
                    return true;
                }
            }

            return (bool) $m->anomalie;
        })->count();
        $ratio = $mesures->count() > 0 ? $horsSeuil / $mesures->count() : 0;
        $pMesures = min(30, round($ratio * 100, 1));

        $incidents = $infra->incidents()->where('date_incident', '>=', now()->subDays(90))->get();
        $charge = $incidents->sum(fn ($i) => (self::POIDS_GRAVITE[$i->gravite] ?? 1) * ($i->statut === 'Résolu' ? 1 : 1.5));
        $pIncidents = min(30, round($charge * 1.5, 1));

        $pEtat = self::POINTS_ETAT[$infra->etat] ?? 0;

        $retards = $infra->maintenances()->where('statut', 'Planifiée')->whereDate('date_prevue', '<', now())->count();
        $pRetard = min(15, $retards * 5);

        $ans = $infra->date_installation ? $infra->date_installation->diffInYears(now(), true) : 0;
        $pAge = min(10, round($ans * 1.0, 1));

        $facteurs = [
            ['libelle' => 'Relevés hors seuil (7 j)', 'points' => $pMesures, 'max' => 30, 'detail' => "{$horsSeuil}/{$mesures->count()} relevés"],
            ['libelle' => 'Incidents pondérés (90 j)', 'points' => $pIncidents, 'max' => 30, 'detail' => $incidents->count().' incident(s)'],
            ['libelle' => 'État opérationnel', 'points' => (float) $pEtat, 'max' => 15, 'detail' => $infra->etat],
            ['libelle' => 'Maintenances en retard', 'points' => (float) $pRetard, 'max' => 15, 'detail' => "{$retards} en retard"],
            ['libelle' => 'Âge de l’installation', 'points' => (float) $pAge, 'max' => 10, 'detail' => round($ans, 1).' an(s)'],
        ];

        $score = (int) min(100, round(array_sum(array_column($facteurs, 'points'))));
        [$niveau, $couleur] = match (true) {
            $score >= 70 => ['Critique', 'danger'],
            $score >= 45 => ['Élevé', 'warning'],
            $score >= 20 => ['Modéré', 'info'],
            default => ['Faible', 'success'],
        };

        return compact('score', 'niveau', 'couleur', 'facteurs');
    }

    /**
     * Infrastructures classées par risque décroissant (propriété dynamique `risque` ajoutée au modèle).
     *
     * @return Collection<int, Infrastructure>
     */
    public function classement(int $limite = 5): Collection
    {
        return Infrastructure::all()
            ->each(fn (Infrastructure $i) => $i->setAttribute('risque', $this->evaluer($i)))
            ->sortByDesc(fn (Infrastructure $i) => $i->risque['score'])
            ->take($limite)
            ->values();
    }
}
