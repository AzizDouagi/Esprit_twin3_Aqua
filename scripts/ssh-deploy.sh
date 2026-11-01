#!/usr/bin/env bash
set -Eeuo pipefail
: "${SSH_HOST:?}" "${SSH_USER:?}" "${SSH_PRIVATE_KEY:?}" "${SSH_KNOWN_HOSTS:?}" "${DEPLOY_PATH:?}" "${APP_IMAGE:?}" "${WEB_IMAGE:?}"
SSH_PORT=${SSH_PORT:-22}
[[ "$SSH_HOST" =~ ^[a-zA-Z0-9.-]+$ && "$SSH_USER" =~ ^[a-zA-Z0-9_-]+$ && "$SSH_PORT" =~ ^[0-9]+$ && "$DEPLOY_PATH" =~ ^/[a-zA-Z0-9_/-]+$ ]] || { echo 'Paramètres SSH invalides' >&2; exit 1; }
for image in "$APP_IMAGE" "$WEB_IMAGE"; do
    [[ "$image" =~ ^ghcr\.io/[a-z0-9._/-]+:sha-[a-f0-9]{40}$ ]] || exit 1
done
ssh_dir=$(mktemp -d)
trap 'rm -rf "$ssh_dir"' EXIT
chmod 700 "$ssh_dir"
printf '%s\n' "$SSH_PRIVATE_KEY" > "$ssh_dir/key"
printf '%s\n' "$SSH_KNOWN_HOSTS" > "$ssh_dir/known_hosts"
chmod 600 "$ssh_dir/key" "$ssh_dir/known_hosts"
options=(-i "$ssh_dir/key" -p "$SSH_PORT" -o BatchMode=yes -o StrictHostKeyChecking=yes -o "UserKnownHostsFile=$ssh_dir/known_hosts")
# Le fichier .env.production est préparé sur le serveur, jamais envoyé depuis GitHub.
tar -czf - docker-compose.prod.yml scripts/deploy.sh | ssh "${options[@]}" "$SSH_USER@$SSH_HOST" "mkdir -p '$DEPLOY_PATH' && tar -xzf - -C '$DEPLOY_PATH'"
ssh "${options[@]}" "$SSH_USER@$SSH_HOST" "cd '$DEPLOY_PATH' && bash scripts/deploy.sh '$APP_IMAGE' '$WEB_IMAGE'"
