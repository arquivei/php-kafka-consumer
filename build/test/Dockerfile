ARG PHP_VERSION

FROM php:${PHP_VERSION}-fpm-alpine

ARG LIBRDKAFKA_VERSION
ARG EXT_RDKAFKA_VERSION
ARG LARAVEL_VERSION

RUN apk update && \
    apk add git zlib-dev libressl-dev libsasl zstd-dev zstd-static build-base && \
    git config --global advice.detachedHead false && \
    apk add bash

RUN git clone --branch ${LIBRDKAFKA_VERSION} https://github.com/edenhill/librdkafka.git && \
    cd librdkafka && \
    ./configure --install-deps && \
    make && \
    make install

RUN apk update && \
    apk add autoconf && \
    pecl install rdkafka-${EXT_RDKAFKA_VERSION}

RUN apk update && \
    apk add libzip-dev unzip && \
    pecl install zip

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN composer global require laravel/installer

WORKDIR /application

RUN cd /application && \
    composer create-project --prefer-dist laravel/laravel laravel-test "${LARAVEL_VERSION}.*"

COPY dev/php.ini /usr/local/etc/php/conf.d

COPY build/composer-files/composer.json-laravel${LARAVEL_VERSION} /application/laravel-test/composer.json

COPY tests /application/laravel-test/tests

COPY build/laravel-kernels/kernel.php /application/laravel-test/app/Console/Kernel.php

COPY dev/php-kafka-consumer.php /application/laravel-test/config

COPY src /application/php-kafka-consumer/src/

COPY tests /application/php-kafka-consumer/tests/

COPY composer.json /application/php-kafka-consumer/composer.json

COPY start.sh /application/php-kafka-consumer/

COPY wait-for-it.sh /application/php-kafka-consumer/

RUN cd /application/php-kafka-consumer && composer update

RUN cd /application/laravel-test && composer update

WORKDIR /application/laravel-test
