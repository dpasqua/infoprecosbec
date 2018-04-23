<?php

// initial config
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

// dependency injection
use DI\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';

$builder = new ContainerBuilder();
$container = $builder->build();
