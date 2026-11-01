<?php

namespace App\Services\AI;

use Illuminate\Support\Str;

/** Prépare les recherches courantes avant génération pour éviter une réponse sans données. */
class ReadOnlyContext
{
    public function collect(array $messages, callable $runTool): array
    {
        $question = (string) end($messages)['content'];
        $text = Str::lower(Str::ascii($question));
        $context = [];
        // Une référence explicite est prioritaire sur un bilan général de risque.
        if (preg_match('/(?:infrastructure|reservoir|station|fiche)\s*#?\s*(\d+)\b/', $text, $match)) {
            $context['infrastructure_status'] = $runTool('infrastructure_status', ['id' => (int) $match[1]]);

            return $context;
        }
        if (preg_match('/\b((?:réservoir|reservoir|station|canalisation|château|chateau)\s+[\p{L}\p{N}][\p{L}\p{N} \-’\']*)/iu', $question, $match)) {
            $found = $runTool('find_infrastructures', ['search' => trim($match[1])]);
            $context['find_infrastructures'] = $found;
            if (count($found['infrastructures']) === 1) {
                $context['infrastructure_status'] = $runTool('infrastructure_status', ['id' => $found['infrastructures'][0]['id']]);
            }

            return $context;
        }
        if (preg_match('/risqu|priorit|surveill/', $text)) {
            $context['risk_ranking'] = $runTool('risk_ranking', []);

            return $context;
        }
        if (preg_match('/dernier|recent|liste|lesquels|lesquelles|montre|detail/', $text)) {
            $scope = $text;
            if (preg_match('/lesquels|lesquelles/', $text)) {
                foreach (array_reverse(array_slice($messages, 0, -1)) as $message) {
                    if ($message['role'] === 'user') {
                        $scope .= ' '.Str::lower(Str::ascii($message['content']));
                        break;
                    }
                }
            }
            if (preg_match('/incident|signalement|critiqu/', $scope)) {
                $filters = [];
                foreach (['critiqu' => 'Critique', 'faible' => 'Faible', 'moyen' => 'Moyenne', 'eleve' => 'Élevée'] as $word => $severity) {
                    if (str_contains($scope, $word)) {
                        $filters['gravite'] = $severity;
                        break;
                    }
                }
                if (preg_match('/actif|non resolu/', $scope)) {
                    $filters['actifs'] = true;
                } elseif (str_contains($scope, 'en cours')) {
                    $filters['statut'] = 'En cours';
                } elseif (str_contains($scope, 'ouvert')) {
                    $filters['statut'] = 'Ouvert';
                } elseif (str_contains($scope, 'resolu')) {
                    $filters['statut'] = 'Résolu';
                }
                $context['recent_incidents'] = $runTool('recent_incidents', $filters);
            } elseif (str_contains($scope, 'infrastructure')) {
                $context['find_infrastructures'] = $runTool('find_infrastructures', []);
            }
        }

        if ($context === [] && str_contains($text, 'infrastructure') && ! str_contains($text, 'incident') && preg_match('/quelles|liste|enregistr|reseau|existe/', $text)) {
            $context['find_infrastructures'] = $runTool('find_infrastructures', []);
        }

        return $context;
    }
}
