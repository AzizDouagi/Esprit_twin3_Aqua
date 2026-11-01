#!/bin/sh
set -eu
if [ -z "${APP_KEY:-}" ]; then
    echo 'APP_KEY doit être fournie au conteneur.' >&2
    exit 1
fi
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public
if [ "${APP_ENV:-local}" = production ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi
exec "$@"
