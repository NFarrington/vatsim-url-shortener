FROM node:10-alpine

USER node

COPY --chown=node:node . /home/node/app

WORKDIR /home/node/app
RUN \
	npm install \
	&& npm run production

FROM php:7.3-fpm-alpine

RUN \
    apk add --update --no-cache --virtual build-dependencies \
        autoconf gcc g++ libtool make \
    && apk add --update --no-cache \
        libmcrypt-dev \
        mysql-client \
        libpng-dev \
        unzip \
    && pecl install mcrypt-1.0.2 \
    && docker-php-ext-enable mcrypt \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install gd \
    && apk del build-dependencies

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

COPY . /var/www/html
COPY --from=0 /home/node/app/public /var/www/html/public
RUN chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

USER www-data

RUN \
    EXPECTED_SIGNATURE="$(curl -s https://composer.github.io/installer.sig)"; \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"; \
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then \
        >&2 echo 'ERROR: Invalid installer signature'; \
        rm composer-setup.php; \
        exit 1; \
    fi; \
    php composer-setup.php --quiet; \
    RESULT=$?; \
    rm composer-setup.php; \
    exit $RESULT

RUN php composer.phar docker-build
USER root

COPY ./docker/app-entrypoint.sh /var/www/html/entrypoint.sh
ENTRYPOINT ["/var/www/html/entrypoint.sh"]
CMD ["php-fpm"]
