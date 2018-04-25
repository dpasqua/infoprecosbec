<?php

// bootstrap
require_once __DIR__ . '/../Bootstrap.php';

use Symfony\Component\Console\Application;

// application setup
$app = new Application();
$app->add($container->get('Infoprecos\BEC\Command\Processar\Municipios'));
$app->add($container->get('Infoprecos\BEC\Command\Processar\UGEs'));
$app->run();
