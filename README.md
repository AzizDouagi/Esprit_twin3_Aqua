# AquaSecure

Application Laravel 12 de supervision d’infrastructures hydrauliques : infrastructures, mesures, maintenance, incidents et aide à la décision. Projet pédagogique d’Aziz Douagi, 5TWIN ESPRIT. Version locale : **v1.1.0**.

## Fonctionnalités

- Front office public : accueil, catalogue, fiches et carte Leaflet.
- Fortify : inscription, vérification email, réinitialisation, profil/avatar et double authentification TOTP ; rôles admin, technicien et user avec policies Spatie.
- Infrastructures : CRUD, corbeille/restauration, géolocalisation, mesures, indicateurs de santé, maintenances et exports CSV/PDF.
- Incidents : déclaration citoyenne, suivi personnel, assignation au personnel actif, workflow audité Ouvert → En cours → Résolu, commentaires, pièces jointes privées, notifications, filtres avancés, tri et exports.
- API `/api/v1` : Sanctum, contrôles de rôle, token respectant la 2FA, Resources, validation et pagination.
- Dashboard : KPI, MTTR depuis la création jusqu’à résolution, incidents par mois/gravité, top infrastructures à risque.
- IA : classification, résumé, bilan mensuel PDF, explication du risque, anomalies statistiques avec validation humaine et chatbot à outils de lecture. Provider **fake déterministe par défaut**, sans clé ni réseau.
- DevOps : images non-root, Compose local/production, CI PHP 8.2/8.3, qualité, couverture en CI, publication GHCR, déploiement SSH conditionnel et rollback documenté.

## Assistant local avec Ollama

Le chatbot dispose d’une interface de conversation, de suggestions, de sources consultées et d’un historique conservé dans l’onglet. Il utilise un vrai modèle local si AI_CHAT_PROVIDER=ollama ; les autres fonctions IA gardent leur propre fournisseur.

```bash
# Dans un terminal, si le service Ollama n’est pas déjà démarré :
ollama serve
# Seulement si le modèle n’est pas installé :
ollama pull gemma4
```

Dans `.env` : `AI_CHAT_PROVIDER=ollama`, `OLLAMA_MODEL=gemma4:latest`, `OLLAMA_BASE_URL=http://127.0.0.1:11434`. Puis `php artisan config:clear` et ouvrir `/admin/ia/chat`. Dans cette session, Ollama a été démarré et le modèle déjà installé est activé. Les réponses réelles ont été vérifiées, notamment les questions de suivi et les chiffres du réseau. Si le modèle manque ou ne répond pas, une réponse de secours ciblée est signalée dans la conversation. Le mode fake ne renvoie plus de JSON brut.

Guide, tests et limites : [Assistant v1.1](docs/ASSISTANT.md). Les résultats v1.0 dans le rapport final restent des preuves historiques.

## Prérequis

PHP **8.2+** avec PDO SQLite ou MySQL, mbstring, xml/dom, fileinfo ; Composer 2 ; Node **22 LTS recommandé** (Vite exige au moins Node 20.19 ou 22.12) ; npm ; Git. Docker Engine/Compose v2 si utilisation conteneurisée. GD, intl, zip, bcmath et pcntl sont intégrés à l’image PHP. Le verrou Composer est résolu pour PHP 8.2 ; les tests ont été exécutés localement sous PHP 8.2 et 8.5.

Les styles Bootstrap, icônes, Chart.js et Leaflet sont compilés par Vite. Les tuiles OpenStreetMap nécessitent Internet ; la liste des infrastructures reste consultable sans fond de carte. mermaid-cli et Chrome/Puppeteer ne sont nécessaires que pour régénérer les diagrammes. Pandoc n’est pas requis.

## Installation sans Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
npm run build
touch database/database.sqlite
```

Configurer `.env` pour une base SQLite **dédiée à ce projet** :

```dotenv
APP_URL=http://127.0.0.1:8000
APP_LOCALE=fr
DB_CONNECTION=sqlite
DB_DATABASE=/chemin/absolu/AquaSecure/database/database.sqlite
DB_URL=
MAIL_MAILER=log
QUEUE_CONNECTION=database
AI_PROVIDER=fake
```

Pour MySQL, créer une base vide `aquasecure` avec un utilisateur dédié ; renseigner DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME et DB_PASSWORD, avec DB_CONNECTION=mysql. Ne jamais réutiliser une base contenant d’autres applications.

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

Dans deux autres terminaux :

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

Avec `MAIL_MAILER=log`, les liens de vérification/réinitialisation sont écrits dans `storage/logs/laravel.log`. Pour les consulter visuellement, utiliser Mailpit via Docker et configurer SMTP vers 127.0.0.1:1025 uniquement si ce port est publié ; le Compose fourni garde SMTP interne aux conteneurs. Les comptes démo sont déjà vérifiés. Sur une installation sans worker, `QUEUE_CONNECTION=sync` permet une démonstration immédiate des notifications.

`php artisan migrate:fresh --seed` **efface la base configurée** : réserver cette commande aux données jetables. Les migrations normales et le déploiement utilisent `migrate --force`. Le seeder n’est pas destiné à la production.

## Installation avec Docker

```bash
cp .env.docker.example .env.docker
# Générer une clé et la copier dans APP_KEY de .env.docker :
php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
make build
make up
make seed
make health
```

Modifier aussi les mots de passe d’exemple avant usage. L’application sera sur **http://localhost:8080**, Mailpit sur **http://localhost:8025**. Pour phpMyAdmin : `docker compose --env-file .env.docker --profile tools up -d phpmyadmin` puis http://localhost:8081. `make down` conserve les volumes. Queue, scheduler et migration sont des services dédiés. Le daemon doit être actif pour les commandes build/up.

La production utilise **seulement** `docker-compose.prod.yml`, avec `.env.production` sur le serveur et les images GHCR. Configuration des secrets, TLS, environnements et rollback : [dossier DevOps](docs/DEVOPS.md). Aucun déploiement ni push n’a été réalisé dans cette session.

## Comptes de démonstration

Mot de passe commun, uniquement pour la base seedée : **Password#2026**.

| Rôle | Email | Parcours |
|---|---|---|
| Administrateur | admin@aquasecure.test | Tous les modules, utilisateurs et corbeille |
| Technicien | technicien@aquasecure.test | Dashboard, incidents, maintenance et IA ; pas de gestion utilisateurs ni suppression d’infrastructure |
| Utilisateur | user@aquasecure.test | Mon espace, déclaration, classification et suivi de ses signalements ; `/admin` renvoie 403 |
| Invité | aucun compte | Accueil, carte et consultation publique |

Le seed crée 12 infrastructures, 37 incidents, 14 maintenances et 2 892 relevés sur 30 jours. Les valeurs et dates sont générées ; elles changent à chaque réinitialisation.

## Routes et tâches utiles

| URL / commande | Usage |
|---|---|
| `/`, `/infrastructures`, `/carte` | Consultation publique |
| `/login`, `/profil/securite` | Connexion et activation 2FA |
| `/declarer-un-incident`, `/mes-signalements` | Déclarer et suivre |
| `/admin` | Dashboard et navigation métier |
| `/admin/ia/chat`, `/admin/ia/anomalies` | Assistant et validation des suggestions |
| `/api/v1/auth/token`, `/api/v1/me` | API authentifiée |
| `/up`, `/health` | Liveness et readiness SQL |
| `php artisan mesures:detect-anomalies` | Détecter sans ouvrir automatiquement d’incident |
| `php artisan maintenances:rappels` | Rappels planifiés, une fois par maintenance |

Voir [API v1](docs/api.md) et [fonctionnement/limites de l’IA](docs/IA.md). Pour Anthropic, renseigner AI_PROVIDER=anthropic, ANTHROPIC_API_KEY et AI_MODEL ; l’interface indique le fournisseur et tout fallback local. Aucun vrai appel distant n’a été validé ici.

## Qualité et vérification

```bash
composer ci                     # Pint --test, PHPStan niveau 5, suite de tests
npm ci && npm run build
composer audit --locked
npm audit --audit-level=high
php scripts/check-yaml.php
python3 scripts/smoke.py --base-url http://127.0.0.1:8000
```

Le smoke HTTP exige une base de démonstration seedée avec les comptes ci-dessus et le provider fake ; il ouvre puis révoque un token, génère des réponses IA et ne crée pas d’incident métier. Résultats et limites détaillés : [validation réelle](docs/VALIDATION.md). PHPStan ne contient aucune baseline. Les tests isolent leurs données ; les tests HTTP en mémoire n’exigent pas le build Vite, vérifié séparément dans le navigateur et en CI.

## Workflow Git

Une branche par phase, commits Conventional Commits avec Co-Authored-By, vérifications puis merge `--no-ff` sur main et tag de version. Tags historiques : v0.1.0 utilisateurs, v0.2.0 infrastructures, v0.3.0 incidents, v0.4.0 API/dashboard, v0.5.0 IA, v0.6.0 DevOps, v1.0.0 finalisation, v1.1.0 assistant Ollama. Les branches et tags sont locaux tant qu’ils ne sont pas poussés.

```bash
git config core.hooksPath .githooks
git switch -c feature/mon-sujet
composer ci
git add chemins-explicites
git commit -m "feat(module): comportement obtenu" -m "Co-Authored-By: Codex <noreply@openai.com>"
git switch main
git merge --no-ff feature/mon-sujet
git log --graph --decorate --oneline --all
```

La CI est réutilisée par la CD sur main/tags. Les étapes SSH sont désactivées tant que DEPLOY_ENABLED n’est pas true ; l’approbation production doit être configurée dans les environnements GitHub. Aucun secret `.env`, clé IA ou clé SSH n’est versionné.

## Livrables de soutenance

- [Grille de validation](GRILLE_VALIDATION.md) et [scénario de démonstration ~10 min](DEMO_VALIDATION.md).
- [Rapport final, auto-évaluation et Q/R](docs/RAPPORT_FINAL.md).
- [Dossier DevOps](docs/DEVOPS.md), [PDF](docs/AquaSecure_DevOps.pdf), sources Mermaid et exports dans `docs/diagrams/`.
- [Captures de vérification](docs/evidence/) : dashboard desktop/mobile et classification mobile.

Régénérer le dossier : `sh scripts/render-diagrams.sh` puis `php scripts/export-docs.php`.
