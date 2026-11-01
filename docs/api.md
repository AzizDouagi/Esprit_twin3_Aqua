# API REST AquaSecure — v1

Base : `/api/v1` · Authentification : **Laravel Sanctum** (jeton Bearer) · Format JSON · Pagination Laravel (`data`, `links`, `meta`).

## Authentification
```bash
curl -X POST http://localhost:8000/api/v1/auth/token \
  -H 'Accept: application/json' \
  -d email=admin@aquasecure.test -d password='<mot de passe du seeder>' -d device_name=cli
# => {"token":"1|...","token_type":"Bearer"}
```
Puis `Authorization: Bearer <token>` + `Accept: application/json`. `DELETE /auth/token` révoque le jeton courant ; `GET /me` renvoie rôles et permissions. Limites : 10 req/min sur `/auth/token`, 120 req/min ensuite.

## Endpoints
| Méthode | URL | Permission | Description |
|---|---|---|---|
| GET | `/infrastructures` | `admin.access` | Liste paginée (`etat`, `type`, `search`, `per_page`≤100) |
| POST | `/infrastructures` | `infrastructures.create` | Création (validation FR) |
| GET/PUT/DELETE | `/infrastructures/{id}` | view / `infrastructures.update` / `infrastructures.delete` | Détail, modification, suppression (soft delete) |
| GET | `/infrastructures/{id}/mesures` | `admin.access` | Relevés (`du`, `au`, `anomalie`, `per_page`≤200) |
| POST | `/infrastructures/{id}/mesures` | `admin.access` | Ingestion d'un relevé capteur |
| GET | `/mesures/{id}` | `admin.access` | Détail d'un relevé |
| GET | `/incidents` | `incidents.manage` | Liste filtrable (`statut`, `gravite`, `infrastructure_id`, `technicien_id`, `du`, `au`, `search`, `tri`, `sens`) |
| POST | `/incidents` | `incidents.manage`/`incidents.report` | Création (le déclarant = utilisateur du jeton) |
| GET/PUT | `/incidents/{id}` | policy `IncidentPolicy` | Détail / modification |
| DELETE | `/incidents/{id}` | rôle admin | Suppression |

Les autorisations sont appliquées côté serveur par les mêmes Policies / Form Requests que le back office. Erreurs : `401` (non authentifié), `403` (interdit), `404`, `422` (validation, corps `{"message","errors":{...}}`), `429` (limite de débit).

## Exemples
```bash
curl -H "Authorization: Bearer $T" -H 'Accept: application/json' \
  'http://localhost:8000/api/v1/incidents?gravite=Critique&per_page=5'

curl -X POST -H "Authorization: Bearer $T" -H 'Accept: application/json' \
  -d ph=7.2 -d pression=4.1 http://localhost:8000/api/v1/infrastructures/1/mesures
```
Tests : `tests/Feature/Api/ApiV1Test.php`.
