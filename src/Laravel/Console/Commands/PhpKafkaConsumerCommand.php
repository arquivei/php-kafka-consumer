<?php

namespace Kafka\Consumer\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Kafka\Consumer\Laravel\Console\Commands\PhpKafkaConsumer\Options;
use Kafka\Consumer\Validators\Commands\PhpKafkaConsumer\Validator;

class PhpKafkaConsumerCommand extends Command
{
    protected $signature = 'arquivei:php-kafka-consumer {--topic=*} {--consumer=} {--groupId=} {--commit=} {--dlq=} {--maxMessage=}';
    protected $description = 'An Apache Kafka consumer in PHP';

    private $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('php-kafka-consumer');
    }

    public function handle()
    {
        (new Validator())->validateOptions($this->options());
        $options = $this->options();
        $options['groupId'] = $options['groupId'] ?? $this->config['groupId'];
        $options = new Options($options);

        $consumer = $options->getConsumer();
        $config = new \Kafka\Consumer\Entities\Config(
            new \Kafka\Consumer\Entities\Config\Sasl(
                $this->config['sasl']['username'],
                $this->config['sasl']['password'],
                $this->config['sasl']['mechanisms']
            ),
            $options->getTopics(),
            $this->config['broker'],
            $options->getCommit(),
            $options->getGroupId(),
            new $consumer(),
            $this->config['securityProtocol'],
            $options->getDlq(),
            $options->getMaxMessage()
        );

        (new \Kafka\Consumer\Consumer($config))->consume();
    }
}
