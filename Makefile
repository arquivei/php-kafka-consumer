include .env

CURRENT_DIRECTORY := $(shell pwd)

up:
	@docker-compose up -d

stop:
	@docker-compose stop

restart: stop up

build:
	@docker-compose up -d --build

tail:
	@docker-compose logs -f

php:
	@docker-compose exec php-fpm bash

test:
	@docker-compose exec php-fpm ./vendor/phpunit/phpunit/phpunit tests

coverage:
	@docker-compose exec \
	    php-fpm \
	    phpdbg -qrr ./vendor/bin/phpunit \
	    --whitelist src/ \
	    --coverage-html coverage/

composer-install:
	@docker-compose exec php-fpm composer install

.PHONY: up stop restart build tail php test coverage
