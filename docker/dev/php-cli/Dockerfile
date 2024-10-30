FROM php:8.3-cli-alpine

RUN apk update && apk upgrade
RUN apk --no-cache add autoconf g++ make && pecl install xdebug && docker-php-ext-enable xdebug && rm -rf /tmp/pear; apk del autoconf g++ make
RUN apk add libpq-dev
RUN apk add icu-dev
RUN apk add --no-cache mysql-client

RUN docker-php-ext-configure intl

RUN docker-php-ext-install pdo pdo_pgsql pgsql intl pdo_mysql

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY ./dev/php/conf.d /usr/local/etc/php/conf.d


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

WORKDIR /app

USER app
