# php-kafka-consumer

[![Latest Stable Version](https://poser.pugx.org/arquivei/php-kafka-consumer/v/stable)](https://packagist.org/packages/arquivei/php-kafka-consumer) [![Total Downloads](https://poser.pugx.org/arquivei/php-kafka-consumer/downloads)](https://packagist.org/packages/arquivei/php-kafka-consumer) ![Tests](https://github.com/arquivei/php-kafka-consumer/workflows/Test/badge.svg) ![Dependency coverage](https://github.com/arquivei/php-kafka-consumer/workflows/Version%20test/badge.svg)

An Apache Kafka consumer in PHP. Subscribe to topics and define callbacks to handle the messages.

## Requirements

In order to use this library, you'll need the [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) PECL extension.
Please notice that the extension requires the [librdkafka](https://github.com/edenhill/librdkafka) C library.

Minimum requirements:

| Dependency  | version |
|-------------|---------|
| librdkafka  | v1.5.3  |
| PHP         | 7.4 +   |
| ext-rdkafka | 3.0 +   |
| Laravel     | 6 +     |

## Install

Using composer:

`composer require arquivei/php-kafka-consumer`

## Usage

```php
<?php

require_once 'vendor/autoload.php';

use Kafka\Consumer\ConsumerBuilder;
use Kafka\Consumer\Entities\Config\Sasl;

class DefaultConsumer
{
    public function __invoke(string $message): void
    {
        print 'Init: ' . date('Y-m-d H:i:s') . PHP_EOL;
        sleep(2);
        print 'Finish: ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}

$consumer = ConsumerBuilder::create('broker:port', 'php-kafka-consumer-group-id', ['topic'])
    ->withSasl(new Sasl('username', 'pasword', 'mechanisms'))
    ->withCommitBatchSize(1)
    ->withSecurityProtocol('security-protocol')
    ->withHandler(new DefaultConsumer()) // or any callable
    ->build();

$consumer->consume();
```

Or by using the legacy API:

```php
<?php

require_once 'vendor/autoload.php';

use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Entities\Config\Sasl;

class DefaultConsumer extends Consumer
{
    public function handle(string $message): void
    {
        print 'Init: ' . date('Y-m-d H:i:s') . PHP_EOL;
        sleep(2);
        print 'Finish: ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}

$config = new Config(
    new Sasl(
        'username',
        'password',
        'mechanisms'
    ),
    ['topic'],
    'broker:port',
    1,
    'php-kafka-consumer-group-id',
    new DefaultConsumer(),
    'PLAINTEXT',
    'topic-dlq',
    1,
    6
);

$consumer = new \Kafka\Consumer\Consumer($config);
$consumer->consume();
```

## Usage with Laravel

You need to add the `php-kafka-consig.php` in `config` path:

```php
<?php

return [
    'topic' => 'topic',
    'broker' => 'broker',
    'groupId' => 'group-id',
    'securityProtocol' => 'security-protocol',
    'sasl' => [
        'mechanisms' => 'mechanisms',
        'username' => 'username',
        'password' => 'password',
    ],
];

```

Use the command to execute the consumer:

```bash
$ php artisan arquivei:php-kafka-consumer --consumer="App\Consumers\YourConsumer" --commit=1
```

### Middlewares

Middlewares are simple callables that receive two arguments: the message being handled and the
next handler. Some possible use cases for middlewares: message transformation, filtering, logging stuff,
or even transaction handling, your imagination is the limit.

```php
<?php

use Kafka\Consumer\ConsumerBuilder;

$consumer = ConsumerBuilder::create('broker:port', 'php-kafka-consumer-group-id', ['topic'])
    ->withHandler(function ($message) {/** ... */})
    // You may add any number of middlewares, they will be executed in the order provided
    ->withMiddleware(function (string $rawMessage, callable $next): void {
        $decoded = json_decode($rawMessage, true);
        $next($decoded);
    })
    ->withMiddleware(function (array $message, callable $next): void {
        if (! isset($message['foo'])) {
            return;
        }
        $next($message);
    })
    ->build();

$consumer->consume();
```

## Build and test

If you want to contribute, there are a few utilities that will help.

First create a container:

`docker compose up -d --build`

If you have make, you can use pre defined commands in the Makefile

`make build`

Then install the dependencies:

`docker compose exec php-fpm composer install`

or with make:

`make composer install`

You can run tests locally:

`docker compose exec php-fpm ./vendor/phpunit/phpunit/phpunit tests`

or with make:

`make test`

and check for coverage:

`docker compose exec php-fpm phpdbg -qrr ./vendor/bin/phpunit --whitelist src/ --coverage-html coverage/`

or with make:

`make coverage`
