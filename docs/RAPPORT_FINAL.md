# AquaSecure — rapport final

**Auteur : Aziz Douagi — 5TWIN ESPRIT. Livraison locale : v1.0.0. Date : 1 novembre 2026.**

## Résultat et objectif d’évaluation

Le projet réunit gestion des utilisateurs, infrastructures, incidents, API, dashboard, fonctions d’aide à la décision et chaîne DevOps. Le dépôt a été repris avec les tags v0.1.0 à v0.4.0 déjà présents, sur feature/ai-core avec trois premiers fichiers de client IA et sa configuration non commités. Ce travail a été conservé et complété ; l’historique n’a pas été réécrit.

Cible ≥18/20 : auto-évaluation prudente **18,5/20**, selon la grille historique fournie dans le projet. La note dépend du jury, de la démonstration et de toute exigence de contribution collective non documentée. La cible n’est pas une garantie. Détail chiffré : [GRILLE_VALIDATION.md](../GRILLE_VALIDATION.md).

## Récapitulatif par phase et preuve

| Phase | Résultat | Preuves et version |
|---|---|---|
| Utilisateurs | Fortify, profils, avatars, vérification email, reset, 2FA, rôles/policies et gestion admin | Actions/Fortify, Auth tests, v0.1.0 existant |
| Infrastructures | Mesures, santé, maintenance, carte, corbeille, CSV/PDF | InfrastructureAdvancedTest, v0.2.0 existant |
| Incidents avancés | Assignation, transitions, historique, commentaires, fichiers privés, déclarations, notifications, filtres/tri et exports | IncidentAdvancedTest ; v0.3.0 existant et relu |
| API/dashboard | API v1 Sanctum, Resources/Requests/pagination, KPI, MTTR, graphiques, risque | ApiV1Test, DashboardTest, docs/api.md ; v0.4.0 existant |
| Socle IA | Interface provider, client Anthropic, fake, cache par utilisateur, quota, logs de durée/statut, timeout et fallback explicite | AiCoreTest ; merge feature/ai-core |
| Fonctions IA | Classification avec préremplissage, résumé, rapport PDF, risque et explication, détection statistique et suggestions, chatbot de lecture | AiFeaturesTest, docs/IA.md ; v0.5.0 |
| Docker | Multi-stage, PHP-FPM et Nginx non-root, MySQL, queue, scheduler, Mailpit et profil phpMyAdmin local, readiness DB et logs JSON | Deux fichiers Compose validés ; HealthTest |
| CI/CD | PHP 8.2/8.3 + MySQL, Pint, Larastan, tests/couverture, build Vite/Docker, audits, GHCR, SSH staging, production après approbation et rollback | YAML parsé/actionlint ; scripts shell vérifiés |
| Documentation DevOps | Boucle, pipeline, architecture, secrets, exploitation et rollback ; PNG/SVG et PDF | docs/DEVOPS.md, diagrams/, AquaSecure_DevOps.pdf ; v0.6.0 |
| Finalisation | Menus par rôle, assets locaux, mobile, erreurs FR, validation renforcée, README, grille, scénario et rapport | SecurityRegressionTest et captures Chrome ; v1.0.0 |

## Correctifs de cohérence lors de la finalisation

Les accès existants ont été consolidés : un compte désactivé ne peut plus réutiliser une session ou un token ; ses tokens sont révoqués. La création de token exige un email vérifié et un TOTP si la 2FA est confirmée. L’API de déclaration utilisateur impose Ouvert et interdit l’assignation. Une mise à jour classique ne peut plus sauter une transition du workflow. L’assignation refuse les utilisateurs simples et le personnel inactif ; les filtres valident types, périodes et tri. Les popups Leaflet utilisent des nœuds DOM texte pour éviter l’interprétation du nom d’une infrastructure comme du HTML.

La version initiale de composer.lock exigeait PHP 8.3 ou 8.4 pour certains paquets alors que la cible annonçait PHP 8.2. Le verrou est maintenant résolu avec platform.php=8.2.0, Spatie Permission 6.25 et les dépendances compatibles ; tests rejoués. La base de validation est un fichier SQLite temporaire séparé de la base habituelle. Aucun migrate:fresh n’a été lancé sur la base de travail de l’utilisateur.

## Résultats de vérification

- **94 tests / 476 assertions**, verts sous PHP 8.2 et PHP 8.5 ; 71 tests étaient présents lors de la reprise effective (le contexte annonçait 62).
- Pint vert, PHPStan niveau 5 sans baseline, composer validate, audits Composer/npm et build Vite réussis.
- Migration intégrale et seed sur base vide dédiée : 12 infrastructures, 37 incidents, 14 maintenances, 2 892 mesures.
- Serveur Laravel réel : accueil, carte, login, refus admin pour user, API avec token, révocation, up/health, fonctions IA fake et PDF.
- Chrome à 390/1440 px : carte, menus, classification, graphiques, absence de débordement horizontal du document et d’erreur JavaScript. Les tableaux larges défilent dans leurs conteneurs.
- Détecteur exécuté réellement : suggestions créées ; une seconde exécution n’ajoute pas de doublon pour les mêmes mesures. Le nombre dépend des seeders aléatoires.
- Deux Compose valides, YAML parsé, actionlint 1.7.12 valide et scripts shell analysés syntaxiquement. Mermaid exporté en PNG/SVG ; PDF DevOps A4 de 6 pages vérifié avec pdfinfo et aperçu rendu.

Commandes et conditions exactes, y compris les limites de ces résultats : [VALIDATION.md](VALIDATION.md). Le PDF DevOps peut changer de pagination après modification de sa source.

## Préparer l’exécution et les comptes

Suivre [README.md](../README.md) : PHP 8.2+, Composer, Node 22 recommandé, SQLite/MySQL et une base propre. Configurer .env, générer APP_KEY, installer Composer/npm, construire Vite, lancer migrate --seed puis serveur, queue et scheduler. Avec Docker : .env.docker renseigné, make build/up/seed. Les comptes de démo ne doivent pas servir en production.

| Compte | Mot de passe démo | Droits |
|---|---|---|
| admin@aquasecure.test | Password#2026 | Administration complète |
| technicien@aquasecure.test | Password#2026 | Incidents, maintenance, dashboard et IA |
| user@aquasecure.test | Password#2026 | Déclaration, classification et suivi personnel |

## Scénario de soutenance (~10 min)

| Étape | Action | URL | Résultat attendu |
|---|---|---|---|
| 1 (~45 s) | Consulter accueil, catalogue et carte | /, /infrastructures, /carte | Front public personnalisé |
| 2 (~45 s) | User connecté, essai du back office | /login, /admin | Accès refusé 403 |
| 3 (~1 min) | Déclarer une petite fuite avec classification | /declarer-un-incident | Proposition Fuite/Faible à confirmer |
| 4 (~1 min) | Technicien : filtrer, trier, assigner | /admin/incidents | Incident retrouvé et journalisé |
| 5 (~50 s) | Commenter puis suivre les transitions | Détail incident | Historique et résolution |
| 6 (~55 s) | Admin : validation/CRUD et restauration | /admin/infrastructures | Erreurs FR puis modification persistée |
| 7 (~45 s) | KPI, MTTR et score explicable | /admin, fiche → risque | Facteurs et valeurs cohérents |
| 8 (~1 min) | Résumé, PDF mensuel et chat | Détail, rapport, /admin/ia/chat | Fake explicite, PDF, lecture seule |
| 9 (~45 s) | Détecter et consulter les suggestions | Commande, /admin/ia/anomalies | Validation humaine requise |
| 10 (~45 s) | Appel API avec token puis révocation | /api/v1 | Pagination, permissions, 401 après révocation |
| 11 (~1 min 30) | Tests, graphe Git et dossier DevOps | Terminal et PDF | Preuves, versions et limites expliquées |

Déroulé détaillé : [DEMO_VALIDATION.md](../DEMO_VALIDATION.md). Préparer les onglets et les commandes avant de démarrer ; ne pas réinitialiser les données en direct.

## Questions possibles du jury

**Pourquoi Laravel/Fortify/Spatie/Sanctum ?** Laravel fournit MVC, Eloquent, validation et tests ; Fortify gère les parcours d’authentification et TOTP ; Spatie centralise les permissions ; Sanctum émet des tokens personnels. Une policy et une FormRequest contrôlent l’action côté serveur même si le menu la masque.

**Comment prouvez-vous le workflow ?** Trois états et transitions explicites. Le endpoint de changement et les mises à jour classiques/API les respectent ; l’observer renseigne résolu_le et l’historique. Des tests refusent un saut direct Ouvert → Résolu.

**Le fake est-il une vraie IA générative ?** Non. C’est un provider déterministe de démonstration basé sur règles et gabarits. Le client Anthropic est implémenté, avec son contrat HTTP testé, mais sans appel distant ni mesure de qualité réelle. Le bandeau indique le fournisseur et les dégradations.

**Comment fonctionnent risque et anomalies ?** Le risque additionne cinq facteurs bornés sur 100 : mesures, incidents, état, retards et âge. Le détecteur compare les seuils puis une fenêtre de mesures antérieures (z-score ou moyenne mobile). Il suggère un incident, qu’un humain valide. Aucun de ces calculs ne certifie l’eau potable ni ne fournit une probabilité calibrée.

**Pourquoi aucun SQL libre dans le chatbot ?** Les outils sont une liste fermée de méthodes Eloquent de lecture et exposent des résultats limités. Un nom d’outil inconnu est refusé. Même si le modèle suit une instruction malveillante, il ne possède aucun outil d’écriture. Les réponses restent à vérifier.

**Comment sont protégées les données ?** Policies pour les actions et téléchargements, fichiers d’incident sur disque privé, validation des entrées, CSRF web, email vérifié, comptes actifs, TOTP au login et à l’émission de token, limites de requêtes et échappement des sorties. L’IA n’enregistre pas les prompts/réponses dans ai_logs ; l’activation du fournisseur distant transmet néanmoins les données choisies à ce fournisseur.

**Que couvre la CI ?** PHP 8.2/8.3, MySQL, style, analyse statique, tests/couverture, build frontend/images et audits. Les YAML ont été validés localement ; les jobs n’ont pas encore tourné sur GitHub. Sans exécution, aucune couverture ou réussite de la matrice distante n’est revendiquée.

**Comment revient-on en arrière ?** Les images portent le SHA de la révision. Le script conserve la release précédente et tente le retour aux images en cas d’échec de smoke. Les migrations ne sont pas inversées automatiquement ; elles doivent être rétrocompatibles et précédées d’une sauvegarde. Une restauration DB demande une procédure opérateur et un arrêt des écritures.

**Pourquoi des tags et merges --no-ff ?** Les tags identifient des jalons ; le graphe conserve les phases. La CD les relie aux images et à la promotion. Les versions sont présentes localement et doivent encore être publiées.

## Limites et actions manuelles restantes

| Sujet | État honnête | Action extérieure nécessaire |
|---|---|---|
| Docker runtime | Configurations valides, daemon arrêté ; aucun build/up local | Démarrer Docker, make build/up, vérifier santé, uploads, queue et scheduler |
| CI/MySQL/PHP 8.3 | Workflow prêt, tests locaux SQLite/PHP 8.2/8.5 | Push/revue puis observer la matrice et artefacts ; corriger toute différence d’environnement |
| GHCR/CD | Scripts prêts, aucune publication ni connexion SSH | Configurer droits packages, accès registry serveur et variables/secrets |
| Production | Environnement et approbation non configurés ici | Créer staging/production, Required reviewers, DEPLOY_ENABLED, DNS/TLS et sauvegardes hors serveur |
| Anthropic réel | Pas de clé, contrat Http::fake seulement | Fournir une clé hors Git, vérifier AI_MODEL et tester coût/latence/qualité sans données sensibles |
| Couverture / supervision | Clover prévu en CI, logs JSON/santé présents | Mesurer la couverture, configurer alertes, collecte et rétention ; aucun Codecov/Sonar activé |
| Évaluation métier | Seuils et risque pédagogiques | Calibrer sur données terrain, examiner faux positifs et absence de relevés |
| Bonus doublons | Jaccard non implémenté | Amélioration facultative après les fonctions obligatoires |
| Git distant | Aucun push effectué | Revoir puis pousser main, branches utiles et tags selon la politique du dépôt |
| Travail collectif et oral | Non attestés par cette session | Présenter répartition réelle des contributions et réaliser la démonstration |

Les secrets requis par environnement GitHub sont SSH_HOST, SSH_USER, SSH_PORT facultatif, SSH_PRIVATE_KEY et SSH_KNOWN_HOSTS ; variable DEPLOY_PATH. Les secrets applicatifs restent sur le serveur (.env.production). La variable de dépôt DEPLOY_ENABLED active les jobs SSH. L’approbation production exige une configuration GitHub effective, documentée dans [DEVOPS.md](DEVOPS.md).
