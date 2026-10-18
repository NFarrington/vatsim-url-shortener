# Image for running the test suite locally, matching the PHP version used in CI.
# Use docker/test/run-tests.sh rather than building this directly.
FROM php:8.0-cli-alpine

RUN apk add --no-cache libpng-dev libzip-dev unzip git \
    && docker-php-ext-install gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN echo 'memory_limit = -1' > $PHP_INI_DIR/conf.d/test.ini \
    && echo 'date.timezone = UTC' >> $PHP_INI_DIR/conf.d/test.ini

WORKDIR /app
