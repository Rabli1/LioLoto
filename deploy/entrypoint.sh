#!/bin/sh
set -eu
cd /var/www/html
PORT=${PORT:-80}
case "$PORT" in
    ''|*[!0-9]*|??????*) echo 'PORT must be a number between 1 and 65535.' >&2; exit 1 ;;
esac
if [ "$PORT" -lt 1 ] || [ "$PORT" -gt 65535 ]; then
    echo 'PORT must be a number between 1 and 65535.' >&2
    exit 1
fi
export PORT

if [ -z "${APP_KEY:-}" ] && [ -n "${APP_KEY_MATERIAL:-}" ]; then
    # Render generates this base64-encoded 256-bit value once and retains it.
    export APP_KEY="base64:${APP_KEY_MATERIAL}"
fi
if [ "${RENDER:-}" = 'true' ]; then
    # Render supplies the included HTTPS onrender.com address; no domain purchase.
    export APP_URL="${RENDER_EXTERNAL_URL:?Render must provide RENDER_EXTERNAL_URL}"
fi
mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# A single replica runs forward migrations; neither image startup nor redeploy resets data.
php scripts/manage.php deploy --skip-install --skip-build
chown -R www-data:www-data storage bootstrap/cache
exec "$@"
