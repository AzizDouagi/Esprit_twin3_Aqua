#!/usr/bin/env bash
# Exécuté sur le serveur depuis DEPLOY_PATH, où .env.production existe déjà.
set -Eeuo pipefail
umask 077
app_image=${1:?app image required}
web_image=${2:?web image required}
for image in "$app_image" "$web_image"; do
    [[ "$image" =~ ^ghcr\.io/[a-z0-9._/-]+:sha-[a-f0-9]{40}$ ]] || { echo 'Image SHA GHCR invalide' >&2; exit 1; }
done
[[ -f .env.production ]] || { echo '.env.production absente' >&2; exit 1; }
exec 9>.deploy.lock
flock -n 9 || { echo 'Un déploiement est déjà en cours' >&2; exit 1; }
candidate=$(mktemp .release.candidate.XXXXXX)
trap 'rm -f "$candidate"' EXIT
printf 'APP_IMAGE=%s\nWEB_IMAGE=%s\n' "$app_image" "$web_image" > "$candidate"
compose() { docker compose --env-file .env.production --env-file "$candidate" -f docker-compose.prod.yml "$@"; }
compose config --quiet
compose pull
compose up -d --wait --wait-timeout 180 mysql
mkdir -p backups
# Sauvegarde avant toute migration. Ne jamais restaurer automatiquement une base en service.
compose exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump --single-transaction --no-tablespaces -uroot "$MYSQL_DATABASE"' | gzip > "backups/pre-deploy-$(date -u +%Y%m%dT%H%M%SZ).sql.gz"
compose run --rm --no-deps migrate
rollback_images() {
    echo 'Échec du smoke test ; retour aux images précédentes si disponibles.' >&2
    if [[ -f .release.env ]]; then
        docker compose --env-file .env.production --env-file .release.env -f docker-compose.prod.yml up -d --no-deps --wait app nginx queue scheduler
    fi
    echo 'Les migrations appliquées restent en place. Vérifier la compatibilité du schéma.' >&2
}
if ! compose up -d --no-deps --wait --wait-timeout 180 app nginx queue scheduler; then
    rollback_images; exit 1
fi
address=$(compose port nginx 8080)
if ! curl --fail --retry 10 --retry-delay 3 "http://$address/up" || ! curl --fail "http://$address/health"; then
    rollback_images; exit 1
fi
if [[ -f .release.env ]]; then cp .release.env .release.previous.env; fi
cp "$candidate" .release.env
printf 'Déploiement validé : %s\n' "$app_image"
