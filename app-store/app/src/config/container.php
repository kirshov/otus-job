<?php
error_reporting(E_ERROR);

/**
 * @var ContainerInterface $container
 */

use App\DTO\SettingsDTO;
use App\Repository\ProductRepository;
use Psr\Container\ContainerInterface;

$container->set('settings', function(){
	return new SettingsDTO(
		redisHost: $_ENV['REDIS_HOST'],
		redisPort: $_ENV['REDIS_PORT'],
		redisPass: $_ENV['REDIS_PASSWORD'],
	);
});


$container->set('db', function(){
	return new PDO(
		'pgsql:host=' . $_ENV['POSTGRES_HOST'] . ';dbname=' . $_ENV['POSTGRES_DB'],
		$_ENV['POSTGRES_USER'],
		$_ENV['POSTGRES_PASSWORD']
	);
});

$container->set('redis', function () {
	$config = [
		'schema' => 'tcp',
		'host' => $_ENV['REDIS_HOST'],
		'port' => $_ENV['REDIS_PORT'],
	];

	$client = new Predis\Client($config);
	$client->auth($_ENV['REDIS_PASSWORD']);

	return $client;
});

$container->set('productRepository', function(ContainerInterface $container){
	return new ProductRepository($container->get('db'));
});

return $container;