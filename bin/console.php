<?php

// initial config
set_time_limit(0);
date_default_timezone_set('America/Sao_Paulo');

// bootstrap // lumen
require_once __DIR__ . '/../bootstrap/app.php';

// dependency injection
use DI\ContainerBuilder;
use Symfony\Component\Console\Application;

$builder = new ContainerBuilder();
$container = $builder->build();

// application setup
$app = new Application();
$app->add($container->get('Infoprecos\BEC\Command\API\OCs'));
$app->add($container->get('Infoprecos\BEC\Command\Processar\Municipios'));
$app->add($container->get('Infoprecos\BEC\Command\Processar\UGEs'));
$app->add($container->get('Infoprecos\BEC\Command\Processar\Coordenadas'));
$app->run();
