<?php

use DI\Container;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$container = new Container();
AppFactory::setContainer($container);

/** @var ContainerInterface $container */
$container = include dirname(__DIR__) . '/src/config/container.php';

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

try {
	$command = $container->get($argv[1]);
	$command();
} catch (Throwable $exception) {
	echo $exception->getMessage();
	exit(1);
}