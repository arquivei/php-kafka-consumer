<?php

namespace Tests;

use \PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::defineIfNotDefined('RD_KAFKA_RESP_ERR_NO_ERROR', 0);
        self::defineIfNotDefined('RD_KAFKA_RESP_ERR__PARTITION_EOF', -191);
        self::defineIfNotDefined('RD_KAFKA_RESP_ERR__TIMED_OUT', -185);
        self::defineIfNotDefined('RD_KAFKA_GENERIC_ERROR', -150);
        self::defineIfNotDefined('RD_KAFKA_RESP_ERR__NO_OFFSET', -168);
        self::defineIfNotDefined('RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT', 7);
        self::defineIfNotDefined('RD_KAFKA_PARTITION_UA', 'undefined');
    }

    private static function defineIfNotDefined(string $key, $value)
    {
        if (!defined($key)) {
            define($key, $value);
        }
    }
}
