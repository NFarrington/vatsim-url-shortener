FROM node:14-alpine AS resources

WORKDIR /home/node/app
RUN chown node:node /home/node/app

USER node

COPY package*.json /home/node/app/

RUN npm ci

COPY --chown=node:node public /home/node/app/public
COPY resources/js /home/node/app/resources/js
COPY resources/sass /home/node/app/resources/sass
COPY webpack.mix.js /home/node/app/

RUN npm run production

########################################

FROM nginxinc/nginx-unprivileged:1.19-alpine AS nginx

USER root

RUN apk add --update --no-cache \
        su-exec \
        curl

COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/server.conf /etc/nginx/conf.d/default.conf
COPY ./docker/server-ssl.conf /etc/nginx/conf.d/default-ssl.conf.disabled

COPY --from=resources /home/node/app/public /var/www/html/public

HEALTHCHECK --start-period=15s --interval=30s --timeout=5s \
    CMD curl -f http://localhost:8081/health || exit 1

EXPOSE 8080 8081 8443

COPY ./docker/nginx-entrypoint.sh /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]

########################################

FROM php:8.0-fpm-alpine AS php-fpm

RUN apk add --update --no-cache --virtual build-dependencies \
        autoconf gcc g++ libtool make \
    && apk add --update --no-cache \
        libmcrypt-dev \
        mysql-client \
        libpng-dev \
        unzip \
        fcgi \
    && pecl install mcrypt-1.0.4 \
    && docker-php-ext-enable mcrypt \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install gd \
    && docker-php-ext-install opcache \
    && docker-php-ext-install pcntl \
    && apk del build-dependencies

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
        && echo 'log_errors_max_len = 0' >> $PHP_INI_DIR/conf.d/app.ini \
        && echo 'cgi.fix_pathinfo = 0' >> $PHP_INI_DIR/conf.d/app.ini \
        && echo 'date.timezone = UTC' >> $PHP_INI_DIR/conf.d/app.ini \
        && echo 'expose_php = 0' >> $PHP_INI_DIR/conf.d/app.ini
COPY ./docker/app-fpm.conf /usr/local/etc/php-fpm.d/zz-app-fpm.conf

WORKDIR /var/www/html

USER www-data

ARG COMPOSER_VERSION=1.10.6
RUN EXPECTED_SIGNATURE="$(curl -s https://composer.github.io/installer.sig)"; \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"; \
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then \
        >&2 echo 'ERROR: Invalid installer signature'; \
        rm composer-setup.php; \
        exit 1; \
    fi; \
    php composer-setup.php --quiet --version $COMPOSER_VERSION; \
    RESULT=$?; \
    rm composer-setup.php; \
    exit $RESULT

COPY composer.* /var/www/html/

RUN php composer.phar docker-install

COPY . /var/www/html
COPY --from=resources /home/node/app/public /var/www/html/public

USER root
RUN chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache
USER www-data

RUN php composer.phar docker-build

ARG APP_COMMIT
ENV APP_COMMIT $APP_COMMIT

ARG APP_VERSION
ENV APP_VERSION $APP_VERSION

HEALTHCHECK --start-period=15s --interval=30s --timeout=5s \
    CMD \
        SCRIPT_NAME=/ping \
        SCRIPT_FILENAME=/ping \
        REQUEST_METHOD=GET \
        cgi-fcgi -bind -connect 127.0.0.1:9000 | tee /dev/stderr | grep pong || exit 1

COPY ./docker/php-fpm-entrypoint.sh /var/www/html/entrypoint.sh
ENTRYPOINT ["/var/www/html/entrypoint.sh"]
CMD ["php-fpm"]
