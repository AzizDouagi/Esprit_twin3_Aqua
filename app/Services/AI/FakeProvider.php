<?php

namespace App\Services\AI;

class FakeProvider implements AiProvider
{
    public function name(): string
    {
        return 'fake';
    }

    public function generate(AiRequest $request): string
    {
        $c = $request->context;
        if ($request->feature === 'classify') {
            $text = mb_strtolower(($c['titre'] ?? '').' '.($c['description'] ?? ''));
            $type = match (true) {
                str_contains($text, 'contamin'), str_contains($text, 'potable'), str_contains($text, 'turbid') => 'Contamination',
                str_contains($text, 'fuite') => 'Fuite',
                str_contains($text, 'pression') => 'Pression anormale',
                str_contains($text, 'coupure') => 'Coupure',
                default => 'Panne équipement',
            };
            $gravite = match (true) {
                $type === 'Contamination', str_contains($text, 'critique') => 'Critique',
                str_contains($text, 'urgent'), str_contains($text, 'important') => 'Élevée',
                str_contains($text, 'petit'), str_contains($text, 'mineur') => 'Faible',
                default => 'Moyenne',
            };

            return json_encode(['type' => $type, 'gravite' => $gravite, 'explication' => 'Suggestion locale par mots-clés ; vérifier avant enregistrement.'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return match ($request->feature) {
            'summary' => sprintf('Incident : %s. Type : %s ; gravité : %s ; statut : %s. %d commentaire(s) enregistré(s). Vérifier les relevés et organiser le suivi avec le technicien.', $c['titre'], $c['type'], $c['gravite'], $c['statut'], $c['commentaires']),
            'report' => sprintf('Bilan %s — %s : %d incident(s), dont %d critique(s), et %d mesure(s). Priorité : contrôler les anomalies et les maintenances en retard.', $c['mois'], $c['nom'], $c['incidents'], $c['critiques'], $c['mesures']),
            'risk' => 'Score heuristique : '.$c['score'].'/100. Examiner les facteurs chiffrés ci-dessous et confirmer les priorités sur le terrain.',
            default => 'Suggestion locale indisponible pour cette fonctionnalité.',
        };
    }

    public function chat(string $system, array $messages, array $tools, callable $runTool): string
    {
        return 'État du réseau (outil de lecture seule) : '.json_encode($runTool('network_stats', []), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
