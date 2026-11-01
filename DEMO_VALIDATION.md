# Démonstration AquaSecure — environ 10 minutes

Préparer une base de démonstration dédiée, `npm run build`, un serveur local, la queue/scheduler ou QUEUE_CONNECTION=sync, et AI_PROVIDER=fake. Exécuter les tests avant la soutenance. Garder trois profils de navigateur distincts (invité, user, admin/technicien) pour éviter les changements de session. Mot de passe démo commun : `Password#2026`.

| Temps | Étape / action | URL ou commande | Résultat observable / critère |
|---|---|---|---|
| 0:00–0:45 | Présenter l’accueil, le catalogue et la carte | `/`, `/infrastructures`, `/carte` | Public sans authentification ; marque, Blade et géolocalisation |
| 0:45–1:30 | Se connecter comme user, puis essayer `/admin` | `/login`, `/admin` | Session vérifiée ; 403 FR ; menus adaptés au rôle |
| 1:30–2:30 | Déclarer « Petite fuite sur canalisation », demander la classification, vérifier et enregistrer | `/declarer-un-incident`, `/mes-signalements` | Type Fuite, gravité Faible proposés par fake ; contrôle humain ; suivi limité à ses propres signalements |
| 2:30–3:30 | Compte technicien : filtrer l’incident par période/gravité/statut et trier ; l’assigner | `/admin/incidents`, détail créé | Filtres combinés, technicien actif, notification et journal |
| 3:30–4:20 | Ajouter commentaire/pièce jointe puis suivre Ouvert → En cours → Résolu | Détail incident | Historique et date de résolution ; export CSV/PDF disponible |
| 4:20–5:15 | Admin : soumettre un formulaire infrastructure invalide, créer/modifier puis archiver/restaurer une fiche de test | `/admin/infrastructures`, `/admin/infrastructures/corbeille` | Erreurs FR, CRUD et soft delete ; ne pas supprimer les données préparées |
| 5:15–6:00 | Lire les KPI, MTTR, graphiques et le risque d’une infrastructure | `/admin`, fiche → Score de risque | Somme explicable, facteurs chiffrés ; score différent d’une probabilité de panne |
| 6:00–7:00 | Générer le résumé et le rapport mensuel PDF ; demander les statistiques au chatbot | Détail incident, fiche infrastructure, `/admin/ia/chat` | Bandeau « Généré par IA », fake explicite, PDF et outils de lecture seule |
| 7:00–7:45 | Lancer le détecteur, ouvrir les suggestions, expliquer la validation humaine | `php artisan mesures:detect-anomalies`, `/admin/ia/anomalies` | Mesure anormale, suggestion unique ; aucune création d’incident sans confirmation |
| 7:45–8:30 | Montrer un token API, liste paginée et 401 sans jeton ; révoquer le jeton | `/api/v1/auth/token`, `/api/v1/incidents`, DELETE token | Contrôle Sanctum et politiques ; ne pas projeter la valeur du token dans un historique partagé |
| 8:30–9:15 | Présenter les contrôles qualité et les versions | `composer ci`, `git log --graph --decorate --oneline --all`, `git tag` | Suite verte, analyse statique, branches et tags ; 2FA montrable via `/profil/securite` si temps |
| 9:15–10:00 | Expliquer Docker et CI/CD avec le diagramme et le PDF | `docs/DEVOPS.md`, `docs/AquaSecure_DevOps.pdf` | Images non-root, CI, staging, approbation et rollback ; distinguer fichiers validés et exécution distante non réalisée |

## Commandes à préparer

```bash
composer ci
npm run build
php artisan route:list --except-vendor
php artisan schedule:list
python3 scripts/smoke.py --base-url http://127.0.0.1:8000
docker compose --env-file .env.docker.example config --quiet
git log --graph --decorate --oneline --all
git tag
```

Ne pas lancer migrate:fresh pendant la démonstration sur une base à conserver. Réinitialiser uniquement la base jetable avant la séance. Les seeders sont aléatoires : les chiffres des captures peuvent différer.

## Si un service manque

- Pas de clé Anthropic : garder fake et montrer les tests `Http::fake` ; ne pas annoncer un appel réel.
- Pas de daemon Docker : montrer `compose config`, Dockerfile et diagrammes, puis préciser que build/up restent à exécuter.
- Pas de réseau cartographique : montrer les points et la liste ; Bootstrap, icônes et graphiques sont locaux.
- CI/CD non configurée : montrer les YAML et scripts, puis la liste des secrets/environnements à créer dans le rapport final.

Les questions du jury et les limites détaillées figurent dans [docs/RAPPORT_FINAL.md](docs/RAPPORT_FINAL.md).
