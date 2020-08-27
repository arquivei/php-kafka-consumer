FROM php:7.1.33-fpm

RUN apt update && apt install -y git && \
    apt install -y libzip-dev && \
    apt install -y unzip && \
    git clone https://github.com/edenhill/librdkafka.git && \
    cd librdkafka && \
    ./configure && \
    make && \
    make install && \
    cd ../ &&\
    pecl install rdkafka-3.1.2 && \
    pecl install zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY dev/php.ini /usr/local/etc/php/conf.d

RUN composer global require laravel/installer

WORKDIR /application

RUN cd /application && \
    composer create-project --prefer-dist laravel/laravel laravel-test

COPY dev/laravel-composer.json /application/laravel-test/composer.json

COPY tests /application/laravel-test/tests

COPY dev/laravel-console-kernel.php /application/laravel-test/app/Console/Kernel.php

COPY dev/php-kafka-consumer.php /application/laravel-test/config

COPY src /application/php-kafka-consumer/src/

COPY tests /application/php-kafka-consumer/tests/

COPY composer.json /application/php-kafka-consumer/composer.json

RUN cd /application/php-kafka-consumer && \
    composer update && \
    cd /application/laravel-test && \
    composer update

WORKDIR /application/laravel-test