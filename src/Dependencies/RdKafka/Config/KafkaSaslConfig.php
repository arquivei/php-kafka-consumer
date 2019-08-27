<?php


namespace Kafka\Consumer\Dependencies\RdKafka\Config;


class KafkaSaslConfig
{
    private $username;
    private $password;
    private $mechanisms;

    public function __construct($username, $password, $mechanisms)
    {
        $this->username = $username;
        $this->password = $password;
        $this->mechanisms = $mechanisms;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getMechanisms()
    {
        return $this->mechanisms;
    }

}