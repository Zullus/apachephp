<?php
$connection = new AMQPConnection([
    'host' => 'rabbitmq',
    'port' => 5672,
    'vhost' => '/',
    'login' => 'guest',
    'password' => 'guest'
]);

var_dump($connection);