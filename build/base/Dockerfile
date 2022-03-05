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

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN composer global require laravel/installer

WORKDIR /application

RUN cd /application && \
    composer create-project --prefer-dist laravel/laravel laravel-test "${LARAVEL_VERSION}.*"
