# Assistant AquaSecure — v1.1

Mise à jour du 1 novembre 2026. Le problème observé était une réponse identique en JSON pour toutes les questions, y compris « bonjour ». Cette version remplace ce comportement et active un vrai modèle local sur cette machine.

## Comportement livré

- Conversation avec messages séparés, formatage sûr du gras/listes, suggestions de questions, indicateur de traitement, annulation, copie, nouvelle conversation et reprise dans le même onglet. Les échanges conservés dans sessionStorage sont séparés par identifiant utilisateur et limités à vingt messages ; aucune table de chat n’a été ajoutée.
- Ollama avec gemma4:latest installé localement. Les autres fonctions IA restent gouvernées par AI_PROVIDER, sans changement de comportement imposé.
- Chiffres frais injectés à chaque question. Total, statut Ouvert, En cours, Résolu, actifs et critiques actifs sont distingués. Les critiques ouverts et les critiques en cours ont des compteurs séparés pour éviter une réponse ambiguë.
- Le modèle peut consulter les cinq outils fermés : network_stats, recent_incidents (filtres et dix résultats maximum avec total exact), find_infrastructures, infrastructure_status (dernier relevé/risque) et risk_ranking (cinq priorités).
- Les recherches courantes (priorités, infrastructure nommée, liste et suivi « lesquels ») sont préparées côté serveur avant génération, afin de fournir des faits même si le modèle ne déclenche aucun outil. Les sources proviennent des outils réellement exécutés côté serveur, avec des liens vers les pages métier. Les KPI de la colonne latérale sont actualisés quand les statistiques sont consultées.
- En cas de panne du modèle, fallback marqué explicitement. Ce secours distingue salutation, total, critiques, résolus, liste d’incidents et risque ; il ne prétend pas être un modèle génératif.

## Lancer et configurer

Ollama est déjà présent sur la machine de développement, avec gemma4 installé. Le service a été démarré sur 127.0.0.1:11434 pendant cette mise à jour, sans téléchargement de modèle. Laravel est disponible sur http://127.0.0.1:8000.

```bash
ollama serve               # un terminal si le service n’est pas déjà lancé
ollama list                # voir les modèles installés
ollama pull gemma4         # uniquement si nécessaire
```

Configuration `.env` (ignorée par Git) :

```dotenv
AI_PROVIDER=fake
AI_CHAT_PROVIDER=ollama
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=gemma4:latest
OLLAMA_TIMEOUT=60
```

Exécuter `php artisan config:clear`, puis ouvrir `/admin/ia/chat` avec un compte administrateur ou technicien. AI_CHAT_PROVIDER vide suit AI_PROVIDER. Les installations sans Ollama gardent un mode démo opérationnel. Aucun appel HTTP sortant au modèle dans les tests automatisés : phpunit.xml force les paramètres de test et les tests Ollama utilisent Http::fake.

La migration intégrale et le seed ont également été vérifiés sur une base SQLite temporaire isolée ; la base de travail a été conservée.

Le modèle peut être changé par OLLAMA_MODEL ; choisir un modèle installé prenant en charge les outils. Avec Laravel dans Docker, 127.0.0.1 désigne le conteneur : fournir une URL de service Ollama accessible depuis le réseau de conteneurs, via les variables d’environnement de ce déploiement. Le service local lancé ici reste lié au loopback de l’hôte ; cette intégration conteneurisée n’a pas été vérifiée.

## Contrat et sécurité

Le client utilise POST /api/chat, messages avec rôle, sorties non streamées, tools natifs Ollama et retours de rôle tool. La limite de réponse totale est 60 secondes par conversation, au plus quatre tours et huit appels d’outils, avec limite de tokens/contexte et raisonnement explicite désactivé. Les chiffres ne viennent jamais d’une exécution de code du modèle. Les noms et paramètres d’outils sont validés ; les paramètres inconnus et les outils SQL/écriture sont refusés. Les données archivées sont exclues des recherches d’infrastructures.

Les routes gardent auth, email vérifié, compte actif, admin.access, ai.use et CSRF. Les textes du modèle sont rendus avec des nœuds DOM, sans innerHTML. Les seules sources cliquables sont les liens métier produits par le serveur, sur la même origine et sous /admin. Les prompts/réponses ne sont pas ajoutés à ai_logs ; celui-ci garde seulement feature, fournisseur, durée, statut et cache. Les titres et relevés demandés sont transmis au modèle configuré : le service activé ici est local.

Un modèle peut encore produire une formulation trompeuse, une erreur de lecture ou un conseil imparfait. Les compteurs et les listes sont récupérés en base, mais la formulation générative ne constitue pas une validation métier. Aucun outil n’autorise une modification. L’annulation dans le navigateur coupe l’attente et l’affichage ; une inférence déjà lancée peut continuer côté serveur jusqu’à sa limite.

## Vérifications réelles

Les mêmes questions que dans la capture utilisateur ont été envoyées via HTTP et Chrome au vrai modèle Ollama, avec provider=ollama et degraded=false :

| Question | Observation sur la base de travail |
|---|---|
| bonjour | Salutation naturelle, aucune sortie JSON |
| combien d’incident | 37 incidents au total, distingués des 29 actifs |
| Combien d’incidents critiques sont ouverts ? | 5 au statut Ouvert, distingués des 7 critiques actifs (5 ouverts + 2 en cours) |
| Et lesquels ? | Consultation de recent_incidents et liste des cinq incidents critiques ouverts, source vers le registre |
| Suggestion « Incidents critiques » | Réponse réelle du modèle local et source consultée |
| Priorités du réseau | Cinq noms, scores et facteurs récupérés en base et reformulés par Ollama |
| État/relevés d’un réservoir nommé | Recherche par nom, état et dernier relevé récupérés ; source vers la fiche |

Ces nombres sont un instantané de la base existante ; aucune réinitialisation de ses données n’a été faite. Un premier appel a pris environ 4,4 secondes ; les comptages suivants environ 1,2 à 1,4 seconde lors du contrôle HTTP. La latence dépend du chargement, de la longueur du contexte et des outils demandés ; ce n’est pas un engagement de performance.

Chrome : contrôles 1440 px et 390 px, envoi par Entrée, suggestion, nouvelle conversation, historique, sources, aucune erreur JavaScript et pas de débordement horizontal. Un texte de réponse contenant une image avec onerror et une source javascript: ont été simulés : aucun HTML n’a été interprété et le lien a été rejeté.

Captures : [accueil assistant](evidence/assistant-welcome.png), [conversation réelle](evidence/assistant-conversation.png), [mobile](evidence/assistant-mobile.png).

`OllamaAssistantTest` ajoute quinze scénarios de régression : salutation, comptages distincts, filtres/limite, recherches/archives, paramètres interdits, contexte et données fraîches, appels d’outils/sources, outil inconnu, panne/fallback, modèle absent historique invalide, préchargement des priorités, contexte de suivi et rejet de réponses contenant des espaces réservés. Suite complète : **109 tests, 542 assertions**, Pint et PHPStan niveau 5 verts. Les preuves v1.0 restent dans VALIDATION.md ; le rapport courant concerne cette évolution ciblée du chatbot.

## Références

[API chat Ollama](https://docs.ollama.com/api/chat) et [outils natifs Ollama](https://docs.ollama.com/capabilities/tool-calling), consultés pour le format des requêtes et des retours d’outils. La présence et le fonctionnement de gemma4 ont été vérifiés sur le service local, pas inférés d’un catalogue.
