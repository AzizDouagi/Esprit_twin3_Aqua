# Validation réelle — AquaSecure v1.0.0

Exécutée localement le **1 novembre 2026** (contexte utilisateur Africa/Tunis). Aucun push, publication GHCR ou déploiement distant. Les résultats ci-dessous décrivent ce qui a été observé ; une configuration CI présente dans le dépôt n’est pas assimilée à une CI exécutée.

## Périmètre et données

Le dépôt avait déjà v0.1.0, v0.2.0, v0.3.0 et v0.4.0 à la reprise, avec **71 tests**. Les fichiers non commités du socle IA ont été conservés. La base de validation a été créée avec `mktemp` dans `/tmp` et configurée uniquement par variables d’environnement de commande. La base habituelle et le fichier .env de l’utilisateur n’ont pas été réinitialisés.

Dernière migration/seed intégrale sur la base jetable : **succès**, sous PHP 8.2.31. Comptages réels : 12 infrastructures, 37 incidents, 14 maintenances, 2 892 mesures. Après analyse : 62 suggestions d’anomalies ; seconde exécution : 0 nouvelle suggestion ; toujours 37 incidents (pas d’ouverture automatique). Le smoke a révoqué son token ; 0 token personnel restant sur cette base de validation.

## Contrôles qualité

| Contrôle | Résultat observé | Limite |
|---|---|---|
| `php artisan test --compact` sous PHP 8.5.6 | **94 tests verts, 476 assertions** | SQLite de test ; pas de matrice distante |
| `composer ci` sous PHP 8.2.31 | **94 tests verts, 476 assertions** ; style et analyse verts | PHP 8.3 non exécuté localement |
| `vendor/bin/pint --test` | Vert | PHP/Blade ne constituent pas un audit fonctionnel complet |
| `vendor/bin/phpstan analyse` | Niveau **5**, aucune erreur, aucune baseline | Paramètre treatPhpDocTypesAsCertain=false ; analyse de app/ |
| `composer validate --strict` | Valide | — |
| `composer audit --locked` | Aucune vulnérabilité signalée au moment du contrôle | Résultat dépendant des avis disponibles |
| `npm ci`, `npm run build` | Succès avec Vite 7.3.7 | Assets construits, pas de budget de performance imposé |
| `npm audit --audit-level=high` | 0 vulnérabilité | État au moment du contrôle |
| Build frontend isolé sans vendor/ ni storage/ | Succès | Vérification du contexte frontend ; ne remplace pas un build Docker |

Le verrou Composer a été résolu pour PHP 8.2 et les tests sont rejoués après le changement de Spatie Permission vers 6.25. Aucun pourcentage de couverture locale n’est annoncé : ni Xdebug ni pcov ne sont actifs localement. La CI prévoit Xdebug et des artefacts Clover/JUnit.

## Serveur et appels HTTP réels

Serveur `php artisan serve` lié à **127.0.0.1:8018**, base temporaire, APP_DEBUG=false, SESSION_DRIVER=file, CACHE_STORE=file, QUEUE_CONNECTION=sync, MAIL_MAILER=log et AI_PROVIDER=fake. Le script `python3 scripts/smoke.py --base-url http://127.0.0.1:8018` a été exécuté avec succès après le dernier seed.

| Parcours | Observation |
|---|---|
| `/`, `/carte`, `/infrastructures`, `/login`, `/up`, `/health` | HTTP 200 |
| Page inexistante | HTTP 404, vue FR |
| API sans authentification | HTTP 401 JSON |
| Login user puis `/admin` | Connexion réussie ; HTTP 403 sur admin |
| Déclaration et POST `/ia/classer` | HTTP 200 ; suggestion Fuite, fournisseur fake |
| Login admin, dashboard, filtres, liste infra, chat et anomalies | HTTP 200 |
| POST `/api/v1/auth/token` | Jeton délivré au compte démo vérifié |
| Listes infra/incidents/mesures avec Bearer | HTTP 200, pagination disponible |
| Résumé incident, score et explication de risque | HTTP 200 en provider fake |
| Rapport mensuel HTML et PDF | HTTP 200 ; signature `%PDF-` vérifiée |
| Chat POST avec outils de lecture | HTTP 200 |
| DELETE token puis GET `/api/v1/me` avec le même token | HTTP 200 à la révocation, puis **401** |
| `php artisan mesures:detect-anomalies` ×2 | 62 nouvelles suggestions puis **0** |
| `php artisan schedule:list` | Détecteur horaire et rappels maintenance à 07:00 présents |
| `php artisan route:list --except-vendor` | Routes métier, API et santé enregistrées |

Le smoke ne valide pas les services réels Anthropic/SMTP ni les migrations MySQL. Les tests de régression vérifient séparément 2FA API, comptes désactivés, révocation des tokens, assignation du personnel, interdiction des sauts de workflow et validation des filtres.

## Navigateur et captures

Chrome headless via Puppeteer a chargé les assets Vite locaux. Vérifications réalisées à **390 px et 1440 px** : accueil, ouverture du menu mobile, initialisation Leaflet, login user, classification avec clic sur le bouton et préremplissage effectif du type, login admin et instances Chart.js du dashboard. Aucun événement pageerror JavaScript ; aucun débordement horizontal global. Les tableaux disposent de leurs propres zones de défilement.

Captures issues de ce contrôle (valeurs des seeders variables) :

- [Accueil mobile](evidence/accueil-mobile.png).
- [Classification mobile](evidence/classification-mobile.png).
- [Dashboard desktop](evidence/dashboard-desktop.png).
- [Dashboard mobile](evidence/dashboard-mobile.png).

Les captures ont été prises avant la dernière réinitialisation de la base de validation ; les noms et statistiques peuvent donc différer des comptages métier calculés ensuite, sans changement de fonctionnalités.

## DevOps et documentation

| Contrôle | Résultat |
|---|---|
| `docker compose --env-file .env.docker.example config --quiet` | Valide |
| Compose prod autonome avec APP_IMAGE/WEB_IMAGE et mots de passe de validation, env_file exemple | Valide, sans affichage de secrets |
| `docker info` | **Échec : daemon absent** (`docker.sock` introuvable) |
| Build/up Docker et smoke conteneurisé | **Non exécutés**, repris par le job images de la CI |
| `php scripts/check-yaml.php` | ci.yml, cd.yml, deploy.yml, dependabot et deux Compose parsés |
| actionlint **1.7.12** | Aucun diagnostic sur les workflows |
| Caches config/routes/vues avec APP_ENV=production et chemins de cache temporaires | Succès, sans remplacer les caches de la base de travail |
| `bash -n` / `sh -n` | Scripts de déploiement, entrypoint, hook et rendu Mermaid valides syntaxiquement |
| `sh scripts/render-diagrams.sh` | Trois sources Mermaid exportées en PNG et SVG via Chrome installé |
| `php scripts/export-docs.php` | PDF DevOps produit par CommonMark + dompdf, sans pandoc |
| `pdfinfo docs/AquaSecure_DevOps.pdf` | PDF 1.7, **6 pages A4**, non chiffré |
| Aperçu PDF via pdftoppm | Pages rendues et diagramme pipeline réorganisé pour rester lisible |
| `.env` et variantes de production | Ignorés ; seuls les fichiers .example sont prévus dans Git |

## Non vérifié et actions à poursuivre hors session

- Appel Anthropic réel : clé absente. Contrat HTTP, outils, panne/timeout/fallback sont simulés via Http::fake. La skill claude-api n’était pas installée ; documentation officielle employée.
- Exécution GitHub Actions, service MySQL de CI, matrice PHP 8.3, génération de couverture et publication GHCR : non exécutées.
- Construction/exécution Docker, permissions réelles des volumes, SMTP Mailpit conteneurisé et processus workers en conteneurs : daemon requis.
- Connexion SSH, backup/restauration réels, promotion staging/production, approbation GitHub et rollback d’images : serveurs/secrets/environnements requis.
- Aucun push. La note de soutenance et la contribution collective ne sont pas vérifiables par ces commandes.

## Reproduire sans toucher à la base habituelle

Créer un fichier vide : `mktemp /tmp/aquasecure-validation.XXXXXX`. Dans un terminal consacré à la validation, définir DB_CONNECTION=sqlite, DB_DATABASE égal au chemin obtenu et DB_URL vide. Vérifier ces valeurs avant `migrate:fresh --seed --force`, puis conserver les mêmes variables pour le serveur, le détecteur et les comptages. Garder le port local libre ; lancer ensuite scripts/smoke.py avec ce port. Utiliser les comptes démo et le provider fake. Les tests PHPUnit ordinaires utilisent une base de test en mémoire via phpunit.xml lorsque ces variables ne sont pas exportées dans leur processus.

Les procédures d’installation et de livraison sont dans [README](../README.md), [DEVOPS](DEVOPS.md) et [RAPPORT_FINAL](RAPPORT_FINAL.md).

## Preuves complémentaires (audit indépendant)

- Images `app` et `web` construites localement, pile Compose démarrée : tous les services healthy, `/up` et `/health` répondent 200.
- Couverture PCOV (PHP 8.3, avec extension GD) : lignes 86,9 %, méthodes 78,5 %.
- Tests API ajoutés : jeton révoqué, déclarant limité à ses incidents, 404 JSON.
