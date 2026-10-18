#!/bin/sh
# Run the PHPUnit test suite (SQLite) in Docker, without installing PHP locally.
#
# Usage: docker/test/run-tests.sh [phpunit args...]
#   e.g. docker/test/run-tests.sh --filter dynamodb --testdox
#
# The working tree is copied into the container, so vendor/ and .env are
# never written to the host. Composer packages are cached in named volumes.
set -e

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
IMAGE=vatsim-url-shortener-test

docker build -q -t "$IMAGE" "$ROOT/docker/test" > /dev/null

COPYFILE_DISABLE=1 tar --no-xattrs --no-mac-metadata -C "$ROOT" \
    --exclude=./.git --exclude=./vendor --exclude=./node_modules \
    --exclude=./.env --exclude='./database/*.sqlite' -cf - . | \
docker run --rm -i \
    -v vatsim-url-shortener-vendor:/app/vendor \
    -v vatsim-url-shortener-composer-cache:/root/.composer/cache \
    "$IMAGE" sh -ec '
        tar -xf - -C /app
        cp .env.ci .env
        touch database/database.tests.sqlite
        composer install --no-ansi --no-interaction --no-progress -q
        php artisan key:generate --no-interaction -q
        vendor/bin/phpunit --configuration phpunit.xml "$@"
    ' sh "$@"
