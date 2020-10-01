CURRENT_DIRECTORY := $(shell pwd)

.PHONY: up stop restart build tail php test coverage version-test-1 version-test-2 version-test-3 version-test-4 version-test-5 version-test-6 version-test-7 version-test-8 version-test-9 version-test-10 version-test-11 version-test-12 version-test-13 version-test-14 version-test-15 version-test-16 version-test-17 version-test-18 version-test-19 version-test-20 version-test-21 version-test-22 version-test-23 version-test-24

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

version-test-1:
	@docker-compose -f docker-compose-test-1.yaml up -d
	@docker-compose -f docker-compose-test-1.yaml exec -T test-1 ./vendor/phpunit/phpunit/phpunit tests

version-test-2:
	@docker-compose -f docker-compose-test-2.yaml up -d
	@docker-compose -f docker-compose-test-2.yaml exec -T test-2 ./vendor/phpunit/phpunit/phpunit tests

version-test-3:
	@docker-compose -f docker-compose-test-3.yaml up -d
	@docker-compose -f docker-compose-test-3.yaml exec -T test-3 ./vendor/phpunit/phpunit/phpunit tests

version-test-4:
	@docker-compose -f docker-compose-test-4.yaml up -d
	@docker-compose -f docker-compose-test-4.yaml exec -T test-4 ./vendor/phpunit/phpunit/phpunit tests

version-test-5:
	@docker-compose -f docker-compose-test-5.yaml up -d
	@docker-compose -f docker-compose-test-5.yaml exec -T test-5 ./vendor/phpunit/phpunit/phpunit tests

version-test-6:
	@docker-compose -f docker-compose-test-6.yaml up -d
	@docker-compose -f docker-compose-test-6.yaml exec -T test-6 ./vendor/phpunit/phpunit/phpunit tests

version-test-7:
	@docker-compose -f docker-compose-test-7.yaml up -d
	@docker-compose -f docker-compose-test-7.yaml exec -T test-7 ./vendor/phpunit/phpunit/phpunit tests

version-test-8:
	@docker-compose -f docker-compose-test-8.yaml up -d
	@docker-compose -f docker-compose-test-8.yaml exec -T test-8 ./vendor/phpunit/phpunit/phpunit tests

version-test-9:
	@docker-compose -f docker-compose-test-9.yaml up -d
	@docker-compose -f docker-compose-test-9.yaml exec -T test-9 ./vendor/phpunit/phpunit/phpunit tests

version-test-10:
	@docker-compose -f docker-compose-test-10.yaml up -d
	@docker-compose -f docker-compose-test-10.yaml exec -T test-10 ./vendor/phpunit/phpunit/phpunit tests

version-test-11:
	@docker-compose -f docker-compose-test-11.yaml up -d
	@docker-compose -f docker-compose-test-11.yaml exec -T test-11 ./vendor/phpunit/phpunit/phpunit tests

version-test-12:
	@docker-compose -f docker-compose-test-12.yaml up -d
	@docker-compose -f docker-compose-test-12.yaml exec -T test-12 ./vendor/phpunit/phpunit/phpunit tests

version-test-13:
	@docker-compose -f docker-compose-test-13.yaml up -d
	@docker-compose -f docker-compose-test-13.yaml exec -T test-13 ./vendor/phpunit/phpunit/phpunit tests

version-test-14:
	@docker-compose -f docker-compose-test-14.yaml up -d
	@docker-compose -f docker-compose-test-14.yaml exec -T test-14 ./vendor/phpunit/phpunit/phpunit tests

version-test-15:
	@docker-compose -f docker-compose-test-15.yaml up -d
	@docker-compose -f docker-compose-test-15.yaml exec -T test-15 ./vendor/phpunit/phpunit/phpunit tests

version-test-16:
	@docker-compose -f docker-compose-test-16.yaml up -d
	@docker-compose -f docker-compose-test-16.yaml exec -T test-16 ./vendor/phpunit/phpunit/phpunit tests

version-test-17:
	@docker-compose -f docker-compose-test-17.yaml up -d
	@docker-compose -f docker-compose-test-17.yaml exec -T test-17 ./vendor/phpunit/phpunit/phpunit tests

version-test-18:
	@docker-compose -f docker-compose-test-18.yaml up -d
	@docker-compose -f docker-compose-test-18.yaml exec -T test-18 ./vendor/phpunit/phpunit/phpunit tests

version-test-19:
	@docker-compose -f docker-compose-test-19.yaml up -d
	@docker-compose -f docker-compose-test-19.yaml exec -T test-19 ./vendor/phpunit/phpunit/phpunit tests

version-test-20:
	@docker-compose -f docker-compose-test-20.yaml up -d
	@docker-compose -f docker-compose-test-20.yaml exec -T test-20 ./vendor/phpunit/phpunit/phpunit tests

version-test-21:
	@docker-compose -f docker-compose-test-21.yaml up -d
	@docker-compose -f docker-compose-test-21.yaml exec -T test-21 ./vendor/phpunit/phpunit/phpunit tests

version-test-22:
	@docker-compose -f docker-compose-test-22.yaml up -d
	@docker-compose -f docker-compose-test-22.yaml exec -T test-22 ./vendor/phpunit/phpunit/phpunit tests

version-test-23:
	@docker-compose -f docker-compose-test-23.yaml up -d
	@docker-compose -f docker-compose-test-23.yaml exec -T test-23 ./vendor/phpunit/phpunit/phpunit tests

version-test-24:
	@docker-compose -f docker-compose-test-24.yaml up -d
	@docker-compose -f docker-compose-test-24.yaml exec -T test-24 ./vendor/phpunit/phpunit/phpunit tests
