<?php
error_reporting(E_ERROR);

/**
 * @var ContainerInterface $container
 */

use App\DTO\SettingsDTO;
use App\Repository\LogsRepository;
use App\Repository\OrdersRepository;
use App\Services\RabbitService;
use Psr\Container\ContainerInterface;

$container->set('settings', function(){
	return new SettingsDTO(
		storeUrl: $_ENV['STORE_URL'],
		billingUrl: $_ENV['BILLING_URL'],
		notifyUrl: $_ENV['NOTIFY_URL'],
		redisHost: $_ENV['REDIS_HOST'],
		redisPort: $_ENV['REDIS_PORT'],
		redisPass: $_ENV['REDIS_PASSWORD'],
		rabbitHost: $_ENV['RABBIT_HOST'],
		rabbitPort: $_ENV['RABBIT_PORT'],
		rabbitUser: $_ENV['RABBIT_USER'],
		rabbitPass: $_ENV['RABBIT_PASSWORD'],
	);
});

$container->set('db', function(){
	return new PDO(
		'pgsql:host=' . $_ENV['POSTGRES_HOST'] . ';dbname=' . $_ENV['POSTGRES_DB'],
		$_ENV['POSTGRES_USER'],
		$_ENV['POSTGRES_PASSWORD']
	);
});

$container->set('ordersRepository', function(ContainerInterface $container){
	return new OrdersRepository($container->get('db'));
});

$container->set('logsRepository', function(ContainerInterface $container){
	return new LogsRepository($container->get('db'));
});

$container->set('rabbitService', function(ContainerInterface $container){
	return new RabbitService($container->get('settings'));
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

return $container;