#!/usr/bin/env sh

set -e

php composer.phar docker-entrypoint

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

exec "$@"
