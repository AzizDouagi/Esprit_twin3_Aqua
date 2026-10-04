# AquaSecure

## Description

AquaSecure est une application Laravel 12 de gestion des infrastructures d'eau potable et des incidents associés. Le projet couvre un module unique : infrastructures et incidents.

## Fonctionnalités

- FrontOffice public : accueil, liste des infrastructures, détail d'une infrastructure.
- BackOffice : dashboard, CRUD Infrastructure, CRUD Incident.
- Relation Eloquent : une infrastructure possède plusieurs incidents.
- Validation serveur avec Form Requests.
- Recherche, filtres, badges visuels et pagination.
- Factories et seeders avec données réalistes.

## Technologies

- Laravel 12
- PHP 8.5
- Blade
- Eloquent ORM
- MySQL
- Bootstrap 5 et Bootstrap Icons
- PHPUnit
- Git

## Prérequis

```bash
php -v
composer --version
node --version
npm --version
mysql --version
git --version
```

## Installation

```bash
cd AquaSecure
composer install
cp .env.example .env
php artisan key:generate
```

## Configuration .env

Configurer la base MySQL dans `.env` :

```dotenv
APP_NAME=AquaSecure
APP_URL=http://127.0.0.1:8000
APP_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aquasecure
DB_USERNAME=root
DB_PASSWORD=
```

## Base de données

Créer la base :

```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS aquasecure CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Lancer les migrations

```bash
php artisan migrate
```

## Seeders

Réinitialiser la base avec les données de démonstration :

```bash
php artisan migrate:fresh --seed
```

## Lancer l'application

```bash
php artisan serve
```

Puis ouvrir :

- FrontOffice : `http://127.0.0.1:8000`
- BackOffice : `http://127.0.0.1:8000/admin`

## Fonctionnalités du module

- Infrastructure : ajout, liste, détail, modification, suppression.
- Incident : ajout, liste, détail, modification, suppression.
- Choix d'une infrastructure lors de la création d'un incident.
- Affichage des incidents liés dans le détail infrastructure.
- Affichage du nom de l'infrastructure dans la liste des incidents.
- Filtres incidents par statut et gravité.
- Recherche textuelle.
- Dashboard avec statistiques.

## Structure

```text
app/
  Http/Controllers/
    DashboardController.php
    FrontOfficeController.php
    InfrastructureController.php
    IncidentController.php
  Http/Requests/
    StoreInfrastructureRequest.php
    UpdateInfrastructureRequest.php
    StoreIncidentRequest.php
    UpdateIncidentRequest.php
  Models/
    Infrastructure.php
    Incident.php
database/
  factories/
  migrations/
  seeders/
resources/views/
  layouts/
  dashboard/
  front/
  infrastructures/
  incidents/
routes/web.php
```

## Démonstration

Voir `DEMO_VALIDATION.md` pour un déroulé de 5 à 8 minutes et `GRILLE_VALIDATION.md` pour la correspondance avec les 20 points.

## Auteur

Aziz Douagi - 5TWIN ESPRIT
