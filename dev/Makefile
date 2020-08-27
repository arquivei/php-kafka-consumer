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

laravel:
	@docker-compose exec laravel bash

test:
	@docker-compose exec laravel ./vendor/phpunit/phpunit/phpunit tests

unit-tests:
	@docker-compose exec laravel ./vendor/phpunit/phpunit/phpunit tests --filter Unit

integration-tests:
	@docker-compose exec laravel ./vendor/phpunit/phpunit/phpunit tests --filter Integration

coverage:
	@docker-compose exec laravel phpdbg -qrr ./vendor/bin/phpunit tests --whitelist /application/php-kafka-consumer/src --coverage-html /application/php-kafka-consumer/coverage

unit-coverage:
	@docker-compose exec laravel phpdbg -qrr ./vendor/bin/phpunit tests --whitelist /application/php-kafka-consumer/src --coverage-html /application/php-kafka-consumer/coverage --filter Unit

integration-coverage:
	@docker-compose exec laravel phpdbg -qrr ./vendor/bin/phpunit tests --whitelist /application/php-kafka-consumer/src --coverage-html /application/php-kafka-consumer/coverage --filter Integration

.PHONY: up stop restart build tail php test coverage
