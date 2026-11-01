# Intelligence artificielle AquaSecure

## Mise à jour v1.1 — assistant Ollama

Le chatbot peut utiliser un fournisseur indépendant `AI_CHAT_PROVIDER=ollama` et le modèle local `OLLAMA_MODEL=gemma4:latest`. Les conversations ont été testées avec le vrai service local, sans clé externe. Le modèle reçoit les chiffres courants puis peut demander des recherches via cinq outils validés. Le mode de secours répond désormais selon la question, sans renvoyer un objet JSON. L’interface affiche des sources, les messages séparément et la progression, et permet d’interrompre, de réessayer ou de recommencer une conversation.

La documentation ci-dessous décrit aussi le socle v1.0 ; le comportement actuel du chatbot et sa validation sont détaillés dans [ASSISTANT.md](ASSISTANT.md). Les autres fonctions gardent AI_PROVIDER, fake par défaut. Le chatbot peut donc être génératif avec Ollama pendant que les boutons de classification restent en mode démonstration.

## Exécution et fournisseurs

`AI_PROVIDER=fake` est le défaut sans réseau : classification par mots-clés, textes issus de gabarits déterministes et chatbot utilisant les statistiques du réseau. Ce mode n’est pas un modèle entraîné. `AI_PROVIDER=anthropic` utilise l’API Messages avec une clé `ANTHROPIC_API_KEY`, le modèle configurable `AI_MODEL=claude-sonnet-5-5`, un timeout de 20 secondes et une limite de sortie de 1024 tokens. Références officielles vérifiées le 1 novembre 2026 : [modèles](https://platform.claude.com/docs/en/models/overview), [Messages API](https://platform.claude.com/docs/en/api/messages/create). La skill claude-api demandée n’était pas installée ; intégration directe via le client HTTP Laravel.

Le fallback local est explicitement marqué `degraded`. Désactiver avec `AI_FALLBACK=false` pour remonter une erreur 503. Une clé absente avec fournisseur distant produit aussi ce fallback. Un JSON malformé est refusé (503) sans préremplissage. Aucun appel Anthropic réel n’a été effectué.

## Fonctionnalités et contrôle humain

- Déclaration et formulaire incident : bouton de classification, JSON validé contre les types et gravités autorisés ; aucun enregistrement automatique.
- Détail incident : résumé du titre, description, type, statut, gravité et nombre de commentaires. Le contenu des commentaires et pièces jointes n’est pas envoyé.
- Infrastructure : rapport mensuel HTML/PDF fondé sur les nombres d’incidents et mesures du mois choisi.
- Risque : heuristique explicable bornée de 0 à 100, indépendante du fournisseur. Explication textuelle facultative. Dashboard déjà relié au même service.
- Anomalies : seuils opérationnels dans `config/aquasecure.php`, puis z-score sur les 30 jours antérieurs (au plus 1000 points, minimum 20) ou écart relatif >35 % sur les 12 points précédents. Le point courant et le futur sont exclus. Une série constante suivie d’une rupture est signalée. Ces seuils sont pédagogiques et doivent être calibrés sur le terrain ; ils ne certifient pas la potabilité.
- `php artisan mesures:detect-anomalies` : exécution horaire, marquage des relevés et suggestions uniques par mesure ; aucune ouverture automatique. Un technicien valide dans `/admin/ia/anomalies`, puis ajuste type et gravité. Une suggestion traitée ne peut pas être validée deux fois.
- Chat back office : cinq outils Eloquent en lecture seule définis dans `ReadOnlyTools` (`network_stats`, `recent_incidents`, `find_infrastructures`, `infrastructure_status`, `risk_ranking`), arguments validés et paramètres inconnus refusés ; dix derniers incidents sans description ni identité, état/dernier relevé et score de risque d’une infrastructure. Aucun SQL libre, outil d’écriture, shell, navigation ou action distante.

## Calcul du risque

Somme arrondie et bornée : relevés hors seuil sur 7 jours `min(30, 100 × proportion)` ; incidents sur 90 jours `min(30, 1,5 × somme des poids)` ; poids gravité 1/2/4/8 et multiplicateur 1,5 si non résolu ; état 0/8/15 ; maintenance planifiée en retard `min(15, 5 × nombre)` ; âge `min(10, années)`. Niveaux : faible <20, modéré <45, élevé <70, critique ≥70. L’absence de relevés vaut zéro point et ne garantit donc pas un faible risque réel. Ce score n’est pas une probabilité ni un modèle ML validé.

## Accès, coûts et données

`ai.use` est attribuée aux trois rôles ; le rôle user accède uniquement à la classification de sa déclaration. Toutes les autres fonctions exigent `admin.access` en plus de la permission IA ; résumé soumis à la policy incident. Authentification, vérification email et CSRF restent actifs. Les sorties Blade sont échappées ; le JavaScript utilise `textContent`. Les textes envoyés au fournisseur restent des données non fiables, mais un modèle peut malgré tout halluciner ou suivre une injection : aucune réponse ne déclenche d’écriture métier.

Limite partagée de 20 appels par utilisateur/minute, y compris cache ; cache de 600 secondes séparé par utilisateur, fournisseur, modèle et contexte. Le chat n’est pas mis en cache. `ai_logs` contient feature, fournisseur, durée, statut et cache, sans clé ni prompt ni réponse. Prévoir une rétention/purge des journaux en production. Les descriptions d’incidents sont transmises à Anthropic si activé : éviter d’y saisir des données personnelles. Aucun historique chat n’est conservé en base par cette fonctionnalité.

## Validation et limites

`AiCoreTest` et `AiFeaturesTest` couvrent fournisseur fake, cache, audit, quota, timeout, fallback, contrat HTTP via `Http::fake`, JSON invalide, génération PDF, permissions, outils de lecture et anomalies/idempotence. La qualité linguistique du fournisseur réel, ses coûts, l’autorisation du modèle et la pertinence des seuils exigent une validation terrain. Le bonus de doublons Jaccard n’est pas implémenté.
