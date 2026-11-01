# Grille de validation AquaSecure — version finale

Correspondance avec la grille sur 20 déjà présente dans le projet. La décision du jury et les exigences collectives éventuelles priment sur cette auto-évaluation.

| Critère | Barème | Éléments livrés et preuve | Démonstration | Auto-évaluation |
|---|---:|---|---|---:|
| Template FrontOffice | 0,5 | Layout front, accueil, catalogue et carte | `/`, `/infrastructures`, `/carte` | 0,5 |
| Template BackOffice | 0,5 | Layout admin, navigation par rôle, dashboard | `/admin` avec admin ou technicien | 0,5 |
| Héritage Blade | 1,5 | Layouts `@yield`, vues `@extends`, composants `@include`, stacks | Montrer layouts/admin et incidents/show | 1,5 |
| Personnalisation | 1,5 | Identité AquaSecure, FR, interfaces responsive, assets locaux | Captures desktop/390 px et menus | 1,5 |
| CRUD entités | 3 | Infrastructure, Incident, maintenance ; corbeille infrastructure | Créer, lire, modifier, supprimer/restaurer | 3 |
| Validation serveur | 2 | FormRequests, règles d’assignation et de workflow, policies | Soumission invalide ; API 422 ; user 403 | 2 |
| Relations Eloquent | 2 | Infrastructure → incidents/mesures/maintenances ; incident → technicien, commentaires et pièces jointes | Incident lié, fiche infrastructure et historique | 2 |
| Factory / Seeder | 2 | Comptes vérifiés, 12 infrastructures, 37 incidents, 14 maintenances, 2 892 mesures | Migration + seed sur base dédiée | 2 |
| Intégration collective | 1 | Application intégrée, tests et documentation ; contribution collective non démontrée par cette session | Lancement reproductible + éléments de travail d’équipe à fournir si requis | 0,5 |
| Git | 3 | Branches par phase, commits, merges --no-ff et tags locaux jusqu’à v1.0.0, .env ignoré | Graphe et tags ; publication distante encore à réaliser | 2,5 |
| Démonstration | 1 | Scénario ~10 min, smoke HTTP et captures Chrome | Présentation orale effective encore à faire | 0,5 |
| Valeur ajoutée | 2 | Fortify/2FA, API Sanctum, workflow, exports, notifications, IA, anomalies et chaîne DevOps | Deux parcours métier, classification, risque, preuves CI/CD | 2 |
| **Total** | **20** | **Évaluation prudente sur preuves locales** | **Aucune note garantie** | **18,5** |

## Preuves complémentaires

- Tests : 94 tests, 476 assertions, PHP 8.2 et PHP 8.5 ; PHPStan niveau 5 sans baseline ; Pint et build Vite verts. Résultats consolidés dans [docs/VALIDATION.md](docs/VALIDATION.md).
- Authentification réelle : invité, utilisateur refusé sur `/admin`, admin connecté ; API avec token puis révocation ; classification, rapport PDF et chatbot fake via HTTP.
- Chrome : contrôles à 390 et 1440 px, menus, carte, graphiques et préremplissage ; captures dans `docs/evidence/`.
- DevOps : Compose valide, workflows parsés/actionlint, Mermaid en PNG/SVG et PDF DevOps généré.

## Réserves explicites

Docker build/up, la CI GitHub/MySQL, la publication GHCR et le déploiement SSH n’ont pas été exécutés ici. Le daemon Docker est arrêté. Anthropic réel nécessite une clé et un contrôle de disponibilité du modèle ; le fake reste un système de règles et gabarits, pas une preuve de qualité d’un modèle génératif. Le score de risque est une heuristique non calibrée terrain. Pas de pourcentage de couverture locale annoncé. Aucun push de branches/tags. Le bonus de doublons Jaccard n’est pas livré.

L’ancienne mention « absence d’authentification » est obsolète : Fortify, profils, vérification, rôles, policies et 2FA sont présents et testés. Les fonctions supplémentaires renforcent le projet sans augmenter artificiellement le barème sur 20.
