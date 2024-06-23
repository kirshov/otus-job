<?php
/**
 * @var ContainerInterface $container
 */

session_start();

use App\Repository\UserRepository;
use App\Services\AuthRedis;
use Psr\Container\ContainerInterface;

$container->set('db', function(){
	return new PDO(
		'pgsql:host=' . $_ENV['POSTGRES_HOST'] . ';dbname=' . $_ENV['POSTGRES_DB'],
		$_ENV['POSTGRES_USER'],
		$_ENV['POSTGRES_PASSWORD']
	);
});

$container->set('userRepository', function(ContainerInterface $container){
	return new UserRepository($container->get('db'));
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

$container->set('authStorage', function (ContainerInterface $container) {
	return new AuthRedis($container->get('redis'));
});

return $container;