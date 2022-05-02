ARG PHP_ARG_VERSION
ARG LARAVEL_VERSION
ARG COMPOSER_AUTH
ARG COMPOSER_ARGS="-o --no-dev --ignore-platform-reqs --no-scripts"

FROM composer AS TestBuilder
ARG LARAVEL_VERSION
ARG COMPOSER_AUTH
ARG COMPOSER_ARGS
COPY build/composer-files/composer.json-${LARAVEL_VERSION} /application/laravel-test/composer.json
WORKDIR /application/laravel-test
RUN composer install $COMPOSER_ARGS

FROM composer AS ConsumerBuilder
ARG LARAVEL_VERSION
ARG COMPOSER_AUTH
ARG COMPOSER_ARGS
COPY composer.json /application/php-kafka-consumer/composer.json
WORKDIR /application/php-kafka-consumer
RUN composer install $COMPOSER_ARGS

FROM php:${PHP_ARG_VERSION}-fpm-alpine

COPY dev/php.ini /usr/local/etc/php/conf.d

WORKDIR /application

COPY --from=TestBuilder /application/laravel-test/vendor /application/laravel-test/vendor
COPY tests /application/laravel-test/tests
COPY build/laravel-kernels/kernel.php /application/laravel-test/app/Console/Kernel.php
COPY dev/php-kafka-consumer.php /application/laravel-test/config

COPY --from=ConsumerBuilder /application/php-kafka-consumer/vendor /application/laravel-test/vendor
COPY src /application/php-kafka-consumer/src/
COPY tests /application/php-kafka-consumer/tests/
COPY start.sh /application/php-kafka-consumer/
COPY wait-for-it.sh /application/php-kafka-consumer/

WORKDIR /application/laravel-test