# Grille de validation AquaSecure

| Critère | Points | Implémentation | Fichier(s) | Route / page | Comment démontrer |
|---|---:|---|---|---|---|
| Template FrontOffice | 0,5 | Layout public, accueil, liste et détail infrastructures | `resources/views/layouts/front.blade.php`, `resources/views/front/*` | `/`, `/infrastructures` | Ouvrir le FrontOffice |
| Template BackOffice | 0,5 | Layout admin, navigation, dashboard, CRUD | `resources/views/layouts/admin.blade.php` | `/admin` | Ouvrir le BackOffice |
| Héritage Blade | 1,5 | `@extends`, `@section`, `@yield`, `@include` | `resources/views/layouts/*`, vues CRUD | Toutes pages | Montrer le code Blade |
| Personnalisation template | 1,5 | Identité AquaSecure, couleurs eau, menus adaptés | Layouts et vues | Front/Back | Montrer navigation et branding |
| CRUD entité | 3 | CRUD complet Infrastructure et Incident | Contrôleurs + vues CRUD | `/admin/infrastructures`, `/admin/incidents` | Ajouter, voir, modifier, supprimer |
| Validation serveur | 2 | Form Requests, règles métier, messages FR, `old()` | `app/Http/Requests/*`, formulaires | Create/edit | Soumettre un formulaire invalide |
| Relations Eloquent | 2 | `hasMany`, `belongsTo`, choix infrastructure, affichages liés | `Infrastructure.php`, `Incident.php`, contrôleurs | Détail infrastructure, liste incidents | Créer un incident lié |
| Factory / Seeder | 2 | 12 infrastructures, 37 incidents reliés | `database/factories/*`, `DatabaseSeeder.php` | Base seedée | Lancer `migrate:fresh --seed` |
| Intégration collective | 1 | Projet cohérent et lançable sur une machine | Ensemble projet | Local | Montrer lancement complet |
| Git | 3 | Dépôt initialisé, commits progressifs, `.env` ignoré | `.gitignore`, historique Git | Terminal | Montrer `git log --oneline` |
| Démonstration | 1 | Scénario de 5 à 8 minutes | `DEMO_VALIDATION.md` | Terminal + navigateur | Suivre le déroulé |
| Valeur ajoutée | 2 | Dashboard stats, filtres, recherche, badges, pagination | Dashboard, contrôleurs, vues | `/admin`, index CRUD | Filtrer et lire les stats |

## Auto-évaluation

Note probable : **19/20**.

Le projet satisfait les critères majeurs : Laravel 12, CRUD complet sur deux entités, validation serveur, relation Eloquent utilisée, factories/seeders, FrontOffice, BackOffice, dashboard, filtres, tests et Git. Le point potentiellement discutable est l'absence d'authentification, volontairement non centrale car le module demandé ne doit pas reposer dessus.
