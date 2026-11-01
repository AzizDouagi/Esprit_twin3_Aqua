# Prompt à donner à Claude Code (projet AquaSecure)

Copie tout ce qui est sous la ligne `---` dans Claude Code, à la racine du projet.

---

Tu es un ingénieur full-stack Laravel / DevOps senior. Tu travailles sur **AquaSecure**, une application Laravel 12 (Blade, MySQL) de supervision d'infrastructures hydrauliques (modules : `Infrastructure` et `Incident`, relation hasMany). Le CRUD, la validation, les factories/seeders, le front office, le back office et le dashboard existent déjà. Lis d'abord `README.md`, `GRILLE_VALIDATION.md`, `DEMO_VALIDATION.md`, `routes/web.php`, `app/`, `resources/views/` et `tests/` pour t'aligner sur le code existant (nommage en français, style Blade, layouts `front` et `admin`).

## Objectif

Le projet est évalué par la grille « Applications Web Avancées 2026/2027 » (validation finale). Je vise **au moins 18/20**. Les critères et leurs points :

| Critère | Points |
|---|---:|
| Fonctionnalités avancées (pour chaque module) | 2 |
| Features IA (adaptées au module) | 3 |
| Intégration avec Git | 1,5 |
| DevOps | 4 |
| Gestion user | 3,5 |
| Cohérence de l'app | 1 |

Détail de « Gestion user » (3,5 pts) :
- Authentification de base (0,5) : inscription, connexion, déconnexion, routes protégées par middleware `auth`, messages d'erreur clairs. Breeze, Jetstream ou Fortify.
- Reset du mot de passe et vérification d'email (1) : lien de réinitialisation envoyé par email et fonctionnel, token avec expiration, `MustVerifyEmail`.
- Rôles et permissions (1) : rôles distincts (admin, user, …), contrôle **côté serveur** (pas seulement dans l'interface). Spatie Permission est un plus.
- 2FA (0,5) : activation et désactivation par l'utilisateur, QR code TOTP et codes de récupération. Jetstream ou Fortify.
- Profil utilisateur (0,5) : modification des informations et du mot de passe, photo de profil, suppression du compte.

Exigences de l'enseignant : authentification (inscription, connexion, déconnexion), au moins deux rôles (utilisateur et administrateur), fonctionnalités avancées pour chaque module, fonctionnalités basées sur l'IA adaptées au module, partie DevOps avec un document contenant (1) un diagramme du processus DevOps et (2) un pipeline des étapes développement → CI → CD.

Fais **tout** le travail ci-dessous de bout en bout, sans me demander de confirmation à chaque étape. Si un choix est ambigu, prends la décision la plus raisonnable et note-la dans le rapport final. Ne casse pas l'existant : les tests actuels doivent continuer à passer.

## Méthode de travail

1. Commence par un plan (liste de tâches) puis exécute-le dans l'ordre des phases ci-dessous.
2. **Git** (1,5 pts) : travaille avec un vrai workflow. Une branche par phase (`feature/auth`, `feature/roles`, `feature/ai-*`, `feature/advanced-*`, `devops/docker`, `devops/ci-cd`, `docs/devops`), commits petits et fréquents au format Conventional Commits (`feat:`, `fix:`, `test:`, `ci:`, `docs:`, `chore:`), merge dans `main` avec `--no-ff`, tags de version (`v1.0.0`, etc.). Ajoute `.github/PULL_REQUEST_TEMPLATE.md`, des templates d'issues et une section « Workflow Git » dans le README. `.env` doit rester ignoré, aucun secret commité. **Ne fais pas `git push`** : je le ferai moi-même. Les commits se terminent par la ligne `Co-Authored-By` demandée par la configuration de la session.
3. À la fin de chaque phase : lance `php artisan test` et `vendor/bin/pint`, corrige tout ce qui échoue, puis commit.
4. Vérifie réellement que ça marche (migrate:fresh --seed, lancement du serveur, appels des routes). Ne déclare jamais « fait » sans avoir exécuté la vérification, et dis honnêtement ce que tu n'as pas pu vérifier (ex. appel réel à une API IA sans clé).

## Phase 1 : Gestion user (3,5 pts)

Choix technique recommandé : **Laravel Fortify** (headless, garde les layouts Blade existants) + **spatie/laravel-permission**. Si Fortify s'avère bloquant, Breeze + 2FA maison ou Jetstream (Livewire) sont acceptables, mais l'interface doit rester cohérente avec l'identité AquaSecure.

- Authentification : pages inscription, connexion, déconnexion en Blade, stylées comme le reste de l'app, messages d'erreur clairs en français, `old()`, throttling (limiter les tentatives).
- Reset du mot de passe : « mot de passe oublié » → email avec lien signé → formulaire de reset. Token avec expiration configurée (`config/auth.php`, `expire` en minutes). En local, utilise **Mailpit** (service docker, voir phase 4) ou `MAIL_MAILER=log`, et documente où voir le mail.
- Vérification d'email : `User implements MustVerifyEmail`, routes admin et espace connecté protégées par `verified`, page « vérifiez votre email », renvoi du lien.
- Rôles et permissions : rôles `admin` et `user` (ajoute un rôle `technicien` si pertinent pour l'assignation d'incidents). Permissions fines (ex. `infrastructures.create|update|delete`, `incidents.manage`, `users.manage`, `ai.use`). Contrôle côté serveur via middleware (`role:`/`permission:`), Policies (`InfrastructurePolicy`, `IncidentPolicy`) et `authorize()` dans les contrôleurs/Form Requests. Le front office public reste accessible, le back office `/admin` est réservé aux admins (et techniciens pour les incidents si tu les ajoutes). Un `user` simple a un espace limité (voir ses signalements, déclarer un incident). Ajoute une page admin de gestion des utilisateurs (liste, changer le rôle, activer/désactiver, supprimer) avec protection : un admin ne peut pas se retirer son propre rôle ni se supprimer.
- 2FA TOTP : page « Sécurité » dans le profil pour activer/désactiver, affichage du QR code, confirmation par un code, affichage et régénération des codes de récupération, challenge 2FA à la connexion (code ou code de récupération).
- Profil : modifier nom/email (re-vérification si l'email change), changer le mot de passe (ancien mot de passe requis), upload de photo de profil (validation image, taille max, stockage `storage/app/public`, `storage:link`, avatar par défaut avec initiales, affichage dans la navbar), suppression du compte avec confirmation par mot de passe.
- Seeders : un admin, un technicien, un user avec identifiants documentés **uniquement dans le seeder et le rapport final** (comptes de démo, mot de passe de test), tous déjà vérifiés pour faciliter la démo.
- Tests Feature pour chaque point : inscription, login, logout, accès refusé (403) pour un user sur `/admin`, redirection des invités, reset, vérification email, 2FA, profil, suppression.

## Phase 2 : Fonctionnalités avancées (2 pts), pour CHAQUE module

**Module Infrastructure** :
- Nouveau modèle `Mesure` (relevés capteurs : débit, pression, niveau, qualité/turbidité, pH, température, `infrastructure_id`, `mesure_le`) avec factory et seeder réalistes (séries temporelles sur 30 jours, avec quelques anomalies injectées).
- Page détail infrastructure avec **graphiques** (Chart.js via npm/Vite ou CDN) des mesures et indicateurs de santé.
- **Carte interactive** Leaflet des infrastructures (ajoute `latitude`/`longitude` par migration, coloration selon l'état), sur le front office et le dashboard.
- **Planning de maintenance** : modèle `Maintenance` (planifiée/réalisée, technicien, date, coût), vue calendrier ou liste filtrable, rappel par notification email/DB.
- Export **CSV et PDF** (barryvdh/laravel-dompdf ou équivalent) de la liste et de la fiche d'une infrastructure.
- Soft deletes et corbeille avec restauration.

**Module Incident** :
- **Assignation** à un technicien, workflow de statut (Ouvert → En cours → Résolu) avec historique des changements (journal d'audit : qui, quoi, quand).
- **Commentaires** sur un incident.
- **Notifications** (mail + base de données) à la création d'un incident critique et à l'assignation, avec la cloche de notifications dans la navbar. Utilise la queue `database` déjà configurée.
- **Pièces jointes / photos** sur un incident.
- Déclaration d'incident par un utilisateur connecté depuis le front office.
- Export CSV/PDF, filtres avancés (période, gravité, statut, infrastructure, technicien), tri.
- **API REST** versionnée (`/api/v1`) avec Laravel Sanctum pour infrastructures, incidents et mesures (Resources, pagination, validation, tests). Documente-la (fichier OpenAPI ou collection Postman/`docs/api.md`).
- Dashboard enrichi : KPI, MTTR (temps moyen de résolution), incidents par gravité/mois, top infrastructures à risque.

## Phase 3 : Features IA (3 pts)

Crée une couche propre : `app/Services/AI/` avec une interface `AiProvider`, un provider réel configurable (Claude via l'API Anthropic en priorité, modèle configurable via `config/ai.php` et `.env` : `AI_PROVIDER`, `ANTHROPIC_API_KEY`, `AI_MODEL`) et un **provider `fake`/local déterministe** utilisé quand aucune clé n'est fournie, afin que la démo fonctionne hors ligne et que les tests n'appellent jamais le réseau (`Http::fake`). Gère timeouts, erreurs, cache et limitation de débit, et journalise les appels (table `ai_logs`: feature, durée, statut). Ne mets **jamais** de clé dans le dépôt : `.env.example` contient des placeholders. Restreins l'usage IA par permission `ai.use`.

Implémente au minimum ces 5 fonctionnalités, toutes visibles dans l'UI :
1. **Classification automatique d'incident** : à partir de la description saisie, propose type et gravité (bouton « Analyser avec l'IA » dans le formulaire, pré-remplit les champs, l'utilisateur valide).
2. **Résumé et rapport d'incident** : résumé d'un incident et de son historique, et génération d'un **rapport mensuel** pour une infrastructure (exportable en PDF).
3. **Prédiction du risque / maintenance prédictive** : score de risque 0-100 par infrastructure calculé à partir des mesures, de l'historique d'incidents et de l'âge (modèle statistique/heuristique documenté, éventuellement enrichi par l'IA pour l'explication en langage naturel). Affiche le score et ses facteurs, et le top des infrastructures à risque au dashboard.
4. **Détection d'anomalies** sur les mesures (z-score ou moyenne mobile, seuils configurables) qui crée automatiquement une alerte/incident suggéré, déclenchable par commande `php artisan mesures:detect-anomalies` et planifiée (scheduler).
5. **Assistant conversationnel** (chatbot) dans le back office : répond en langage naturel aux questions sur les données (ex. « Quelles infrastructures ont le plus d'incidents critiques ce mois-ci ? »). Implémente-le de façon sûre : outils/fonctions en lecture seule exposant des requêtes Eloquent prédéfinies, jamais de SQL libre généré par l'IA. Bonus : détection de doublons d'incidents par similarité.

Pour chaque feature : Form Request, contrôleur dédié, vue, test (avec fake provider), et section dédiée dans la documentation expliquant le fonctionnement et les limites. Indique clairement dans l'UI que le contenu est généré par IA.

## Phase 4 : DevOps (4 pts, la plus lourde)

Livrables attendus, tous fonctionnels et vérifiés :

1. **Docker** : `Dockerfile` multi-stage (build assets Node → image PHP-FPM prod, utilisateur non-root, opcache), `docker-compose.yml` (app, nginx, mysql, queue worker, scheduler, **mailpit**, phpmyadmin optionnel), `docker-compose.prod.yml` si utile, `.dockerignore`, healthchecks, volumes, entrypoint qui lance migrations/optimisations. Un `Makefile` avec `make up`, `make test`, `make lint`, etc. Vérifie avec `docker compose up --build` (si Docker n'est pas disponible dans l'environnement, valide au moins la syntaxe et dis-le explicitement).
2. **CI GitHub Actions** (`.github/workflows/ci.yml`) déclenchée sur push et PR : checkout, setup PHP 8.2/8.3 (matrice), cache composer, service MySQL, `composer install`, `.env` de test, `key:generate`, migrations, **Laravel Pint** (`--test`), **analyse statique Larastan/PHPStan**, **tests PHPUnit avec couverture** (artefact), build des assets Vite, `composer audit` et `npm audit` (sécurité), éventuellement SonarCloud/Codecov (config prête, désactivable sans secret).
3. **CD** (`.github/workflows/cd.yml`) : sur tag `v*` ou push sur `main` après CI verte → build et **push de l'image Docker sur GitHub Container Registry (GHCR)** avec tags (sha, semver, latest), puis déploiement. Prévois un déploiement réaliste et documenté : job de déploiement SSH vers un serveur via `docker compose pull && up -d` **ou** Render/Railway/Fly.io (choisis le plus simple, secrets via GitHub Secrets, jamais en dur), avec environnements `staging` puis `production` (approbation manuelle), smoke test `/up` après déploiement et stratégie de rollback documentée.
4. **Qualité et automatisation** : Dependabot (`.github/dependabot.yml`), hook pre-commit ou `composer scripts` (`lint`, `analyse`, `test`, `ci`), route de santé (`/up` + endpoint `/health` vérifiant la base), logs structurés.
5. **Document DevOps** (obligatoire, c'est ce que l'enseignant demande), `docs/DEVOPS.md` **et** export PDF/DOCX (`docs/AquaSecure_DevOps.pdf`, génère-le via pandoc/mermaid-cli ou un script, ou à défaut fournis le Markdown prêt à convertir et explique comment) contenant :
   - **Un diagramme du processus DevOps** (boucle Plan → Code → Build → Test → Release → Deploy → Operate → Monitor, avec les outils réellement utilisés : GitHub, GitHub Actions, Docker, GHCR, Pint, PHPStan, PHPUnit, Mailpit, etc.) ;
   - **Un diagramme de pipeline** développement → CI → CD, étape par étape (commit/PR → lint → analyse statique → tests → build → audit sécurité → image Docker → push registry → deploy staging → smoke test → approbation → deploy production → monitoring), avec conditions de déclenchement et artefacts ;
   - Le workflow Git, l'architecture Docker, la gestion des secrets, les environnements, la stratégie de rollback, la supervision, et un tableau « critère → preuve dans le dépôt ».
   Les diagrammes sont en Mermaid dans le Markdown **et** exportés en images PNG/SVG dans `docs/diagrams/`.
6. **Supervision minimale** : healthcheck, page/commande de statut, logs ; Laravel Pulse ou Telescope en dev est un plus.

## Phase 5 : Cohérence de l'app (1 pt) et finitions

- Navigation cohérente selon le rôle (menus différents pour invité, user, technicien, admin), thème AquaSecure homogène, messages flash, pages d'erreur 403/404/500 personnalisées, responsive, tout en français.
- `README.md` complet : installation locale (avec et sans Docker), variables `.env`, comptes de démo, commandes, architecture, liste des fonctionnalités.
- Mets à jour `GRILLE_VALIDATION.md` (nouvelle grille avec les 6 critères ci-dessus, fichiers et routes réels, comment démontrer) et `DEMO_VALIDATION.md`.
- `php artisan migrate:fresh --seed` doit fonctionner d'une traite sur une base vide. Suite de tests entièrement verte, Pint propre, PHPStan sans erreur.

## Rapport final obligatoire

Quand tout est terminé, écris `docs/RAPPORT_FINAL.md` **et** affiche-le dans ta réponse. Il doit contenir :

1. **Récapitulatif par critère de la grille** (Gestion user 3,5 / Features IA 3 / DevOps 4 / Fonctionnalités avancées 2 / Git 1,5 / Cohérence 1) : ce qui a été fait, fichiers clés, routes, commandes, et une auto-évaluation honnête.
2. **Prérequis et lancement** : commandes exactes pour installer, configurer `.env` (y compris la clé IA optionnelle), `migrate:fresh --seed`, lancer l'app (local et Docker), lancer les tests.
3. **Comptes de démo** (email / rôle / mot de passe de test).
4. **Scénario de test pas à pas (≈10 minutes)** que je peux suivre devant le jury, dans l'ordre : inscription → vérification email (via Mailpit) → connexion → reset mot de passe → profil et photo → activation 2FA (QR, codes de récupération) → déconnexion/reconnexion avec 2FA → test des rôles (user refusé sur `/admin`, admin accepté, gestion des utilisateurs) → fonctionnalités avancées par module (carte, graphiques, assignation, notifications, exports, API) → chaque feature IA (avec résultat attendu) → démonstration Git (`git log --graph --oneline`, branches, tags) → démonstration DevOps (ouvrir le workflow CI/CD, lancer `docker compose up`, montrer le document et les diagrammes, un run vert). Pour chaque étape : action, URL, résultat attendu.
5. **Questions probables du jury et réponses courtes** (pourquoi Fortify, pourquoi ce provider IA, comment sont sécurisés les rôles, comment fonctionne le pipeline, etc.).
6. **Limites connues et ce qui n'a pas pu être vérifié** (ex. pas de vraie clé IA, pas de déploiement réel, Docker non testé), et les actions manuelles qui me restent (créer les secrets GitHub, `git push`, renseigner `ANTHROPIC_API_KEY`).

Commence maintenant par le plan, puis exécute les phases une par une.
