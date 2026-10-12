#!/bin/sh
set -eu
cd /var/www/html
mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# A single replica runs forward migrations; neither image startup nor redeploy resets data.
php scripts/manage.php deploy --skip-install --skip-build
chown -R www-data:www-data storage bootstrap/cache
exec "$@"
