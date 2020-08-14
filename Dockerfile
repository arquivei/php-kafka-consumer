FROM php:7.1.33-fpm

RUN apt update && apt install -y librdkafka-dev && apt install -y git && apt install -y libzip-dev && apt install -y unzip

RUN pecl install rdkafka

RUN pecl install zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY php.ini /usr/local/etc/php/conf.d

WORKDIR /application