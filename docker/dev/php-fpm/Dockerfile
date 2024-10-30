FROM php:8.3-fpm-alpine

RUN apk update && apk upgrade
RUN apk add --no-cache mysql-client

RUN apk add libpq-dev
RUN apk add icu-dev

RUN docker-php-ext-configure intl

RUN docker-php-ext-install pdo pdo_pgsql pgsql intl pdo_mysql

RUN apk add --update linux-headers --no-cache autoconf g++ make \
    && pecl install xdebug \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable xdebug


RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

USER root


#-------- [CONFIGS] ----------------

# [PHP-.ini]
# xdebug.ini .etc
COPY ./dev/php/conf.d /usr/local/etc/php/conf.d

# [PHP-FPM]
# check config command = php-fpm -tt
# global fpm config
COPY ./dev/php-fpm/php-fpm-conf/php-fpm.conf /usr/local/etc/php-fpm.conf

# copy pools
# php-fpm pools folder -> /usr/local/etc/php-fpm.d
# [docker.conf,www.conf,www.conf.default,zz-docker.conf]
COPY ./dev/php-fpm/fpm-pools/php-fpm.d /usr/local/etc/php-fpm.d

RUN rm /usr/local/etc/php-fpm.d/docker.conf
RUN rm /usr/local/etc/php-fpm.d/zz-docker.conf
#------------------------------------

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

WORKDIR /app

RUN chown -R app:app /app