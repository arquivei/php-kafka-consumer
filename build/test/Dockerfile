ARG TAG

FROM arquivei/php-kafka-consumer:${TAG}

ARG LARAVEL_VERSION

COPY dev/php.ini /usr/local/etc/php/conf.d

COPY build/composer-files/composer.json-${LARAVEL_VERSION} /application/laravel-test/composer.json

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
