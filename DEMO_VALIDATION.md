# Démonstration AquaSecure

Durée cible : 5 à 8 minutes.

| Étape | Ce que je fais | Ce que je dis | Critère validé |
|---|---|---|---|
| 1 | Afficher `git log --oneline` | Le projet a été versionné avec des commits progressifs et significatifs. | Git |
| 2 | Lancer `php artisan serve` | L'application Laravel 12 tourne en local. | Démonstration |
| 3 | Ouvrir `/` | Le FrontOffice public AquaSecure est intégré et personnalisé. | Template FrontOffice |
| 4 | Ouvrir `/infrastructures` puis un détail | Le public peut consulter les infrastructures sans actions admin. | FrontOffice, relation |
| 5 | Ouvrir `/admin` | Le BackOffice affiche les statistiques : infrastructures, incidents, ouverts, critiques. | Dashboard, valeur ajoutée |
| 6 | Ouvrir `/admin/infrastructures` | La liste admin propose recherche, badges, pagination et actions CRUD. | CRUD, template BackOffice |
| 7 | Cliquer Ajouter infrastructure et soumettre vide | Les erreurs serveur en français s'affichent et les anciennes valeurs sont conservées. | Validation |
| 8 | Ajouter une infrastructure valide | L'ajout fonctionne et redirige vers le détail. | CRUD create/read |
| 9 | Modifier l'infrastructure | La modification est persistée en base. | CRUD update |
| 10 | Ajouter un incident lié à cette infrastructure | Le formulaire Incident impose une infrastructure existante. | Relation Eloquent |
| 11 | Afficher la liste incidents | Le nom de l'infrastructure, les badges statut/gravité et les filtres sont visibles. | Relation, valeur ajoutée |
| 12 | Revenir au détail infrastructure | Les incidents associés apparaissent dans la page détail. | hasMany |
| 13 | Montrer `php artisan migrate:fresh --seed` | Les factories et seeders créent des données réalistes reliées. | Bonnes pratiques |
| 14 | Montrer `resources/views/layouts` et une vue CRUD | Les vues utilisent `@extends`, `@section`, `@yield` et `@include`. | Héritage Blade |
| 15 | Supprimer un incident ou une infrastructure | Une confirmation navigateur protège la suppression. | CRUD delete, sécurité |
| 16 | Montrer `php artisan test` | Les tests automatisés valident les principaux parcours. | Fiabilité |

Commandes utiles :

```bash
php artisan about
php artisan route:list
php artisan migrate:fresh --seed
php artisan test
php artisan serve
git log --oneline
```
