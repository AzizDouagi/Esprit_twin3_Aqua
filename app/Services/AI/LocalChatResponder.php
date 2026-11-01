<?php

namespace App\Services\AI;

use Illuminate\Support\Str;

/** Réponses ciblées de secours, sans prétendre être un modèle génératif. */
class LocalChatResponder
{
    public function respond(array $messages, callable $runTool): string
    {
        $text = Str::lower(Str::ascii(trim((string) end($messages)['content'])));
        if (preg_match('/\b(supprim|modifi|effac|assign|ferm|resou|execut|drop|delete|update|insert)/', $text)) {
            return 'Je peux consulter les données et vous aider à décider, mais je ne modifie pas le réseau. Pour intervenir, ouvrez la fiche concernée dans les incidents ou les infrastructures.';
        }
        if (preg_match('/^(bonjour|salut|bonsoir|hello|coucou|merci|bonjour[ !?.]+|salut[ !?.]+)$/', $text)) {
            return str_starts_with($text, 'merci') ? 'Avec plaisir ! Souhaitez-vous vérifier les incidents critiques ou les infrastructures à surveiller ?' : 'Bonjour ! Je peux vous aider à consulter les incidents, les relevés et les infrastructures à surveiller. Que souhaitez-vous savoir ?';
        }
        if (str_contains($text, 'risqu') || str_contains($text, 'priorit') || str_contains($text, 'surveill')) {
            $rows = $runTool('risk_ranking', [])['infrastructures'];
            if ($rows === []) {
                return 'Aucune infrastructure n’est encore enregistrée.';
            }

            return "Voici les infrastructures à surveiller en priorité :\n\n".implode("\n", array_map(fn ($i) => '- '.$i['nom'].' : '.$i['score'].'/100 ('.$i['niveau'].') — '.($i['principal_facteur']['libelle'] ?? 'facteurs à examiner').'.', $rows))."\n\nCes scores sont des indicateurs heuristiques à confirmer sur le terrain.";
        }
        if ((str_contains($text, 'incident') || str_contains($text, 'signalement')) && (str_contains($text, 'dernier') || str_contains($text, 'recent') || str_contains($text, 'liste'))) {
            $filters = str_contains($text, 'critiqu') ? ['gravite' => 'Critique'] : [];
            $result = $runTool('recent_incidents', $filters);
            if ($result['total'] === 0) {
                return 'Aucun incident ne correspond à cette recherche.';
            }

            return "Voici les derniers incidents enregistrés :\n\n".implode("\n", array_map(fn ($i) => '- #'.$i['id'].' · '.$i['titre'].' — '.$i['gravite'].', '.$i['statut'].' ('.$i['date'].').', $result['incidents']));
        }
        if (preg_match('/(?:infrastructure|reservoir|station|fiche)\s*#?\s*(\d+)/', $text, $match)) {
            $result = $runTool('infrastructure_status', ['id' => (int) $match[1]]);
            if (isset($result['message'])) {
                return $result['message'];
            }

            return $result['infrastructure']['nom'].' : '.$result['infrastructure']['etat'].'. '.$result['incidents_actifs'].' incident(s) non résolu(s). Score de risque : '.$result['risque']['score'].'/100.';
        }
        if (str_contains($text, 'combien') || str_contains($text, 'nombre') || str_contains($text, 'bilan') || str_contains($text, 'statist') || str_contains($text, 'etat du reseau') || str_contains($text, 'critiqu')) {
            $s = $runTool('network_stats', []);
            if (str_contains($text, 'critiqu')) {
                if (str_contains($text, 'resolu') && ! str_contains($text, 'non resolu')) {
                    return '**'.$s['incidents_critiques_resolus'].' incident(s) critique(s) sont résolus**.';
                }
                if (str_contains($text, 'en cours')) {
                    return '**'.$s['incidents_critiques_en_cours'].' incident(s) critique(s)** sont en cours de traitement.';
                }
                if (str_contains($text, 'total')) {
                    return '**'.$s['incidents_critiques_total'].' incident(s) critique(s)** sont enregistrés au total, dont '.$s['incidents_critiques'].' encore actifs.';
                }
                if (str_contains($text, 'ouvert') && ! str_contains($text, 'actif')) {
                    return '**'.$s['incidents_critiques_ouverts'].' incident(s) critique(s) sont ouverts**. '.$s['incidents_critiques_en_cours'].' autre(s) sont en cours de traitement, soit '.$s['incidents_critiques'].' critiques actifs au total.';
                }

                return $s['incidents_critiques'] === 0 ? 'Aucun incident critique n’est actuellement actif sur le réseau.' : 'Il y a **'.$s['incidents_critiques'].' incident'.($s['incidents_critiques'] > 1 ? 's critiques actifs' : ' critique actif').'** sur le réseau, c’est-à-dire ouverts ou en cours de traitement.';
            }
            if (str_contains($text, 'infrastructure') && ! str_contains($text, 'incident')) {
                return 'Le réseau compte **'.$s['infrastructures'].' infrastructure(s)** enregistrée(s).';
            }
            if ((str_contains($text, 'resolu') || str_contains($text, 'resolut')) && ! str_contains($text, 'non resolu')) {
                return '**'.$s['incidents_resolus'].' incident(s) sont résolus** sur '.$s['incidents_total'].' incidents enregistrés.';
            }
            if (str_contains($text, 'en cours')) {
                return '**'.$s['incidents_en_cours'].' incident(s)** sont en cours de traitement.';
            }
            if (str_contains($text, 'ouvert') || str_contains($text, 'actif') || str_contains($text, 'non resolu')) {
                return '**'.$s['incidents_actifs'].' incidents sont actifs** : '.$s['incidents_ouverts'].' ouverts et '.$s['incidents_en_cours'].' en cours de traitement.';
            }

            return 'Le réseau compte **'.$s['incidents_total'].' incidents au total** : '.$s['incidents_ouverts'].' ouverts, '.$s['incidents_en_cours'].' en cours et '.$s['incidents_resolus'].' résolus. Parmi les incidents actifs, **'.$s['incidents_critiques'].' sont critiques**.';
        }
        if (str_contains($text, 'infrastructure')) {
            $rows = $runTool('find_infrastructures', [])['infrastructures'];

            return $rows === [] ? 'Aucune infrastructure enregistrée.' : "Voici les infrastructures du réseau :\n\n".implode("\n", array_map(fn ($i) => '- #'.$i['id'].' · '.$i['nom'].' — '.$i['etat'].'.', $rows))."\n\nPrécisez un identifiant pour consulter ses relevés.";
        }

        return 'Je peux vous donner le nombre d’incidents, lister les derniers signalements ou identifier les infrastructures à risque. Précisez votre demande, par exemple « Combien d’incidents critiques sont actifs ? ».';
    }
}
