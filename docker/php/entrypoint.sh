#!/bin/sh
set -e

# var/ is a named volume, not part of the bind mount: a cache built on the host
# holds host paths and is useless (usually fatal) in here. The volume starts out
# empty and root-owned, so php-fpm's www-data needs it handed over.
mkdir -p var public/uploads
chown -R www-data:www-data var public/uploads 2>/dev/null || true

if [ ! -f vendor/autoload_runtime.php ]; then
    echo "vendor/ is missing — running composer install"
    composer install --no-interaction --prefer-dist
fi

exec docker-php-entrypoint "$@"
