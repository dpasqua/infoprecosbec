<?php

// bootstrap
require_once __DIR__ . '/../Bootstrap.php';

use Symfony\Component\Console\Application;
require_once __DIR__ . '/../src/Command/Crawler/Municipios.php';

// application setup
$app = new Application();
$app->add($container->get('Command\Crawler\Municipios'));
$app->run();
