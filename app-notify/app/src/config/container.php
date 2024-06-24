<?php
error_reporting(E_ERROR);

/**
 * @var ContainerInterface $container
 */
use App\Console\NotifySender;
use App\Consumers\NotifyConsumer;
use App\DTO\SettingsDTO;
use App\Repository\NotifyRepository;
use App\Services\RabbitService;
use Psr\Container\ContainerInterface;

$container->set('settings', function(){
	return new SettingsDTO(
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

$container->set('notifyRepository', function(ContainerInterface $container){
	return new NotifyRepository($container->get('db'));
});

$container->set('notifySender', function(ContainerInterface $container){
	return new NotifySender($container->get('notifyRepository'));
});

$container->set('rabbitService', function(ContainerInterface $container){
	return new RabbitService($container->get('settings'));
});

$container->set('notifyConsumer', function(ContainerInterface $container){
	return new NotifyConsumer(
		$container->get('rabbitService'),
		$container->get('notifyRepository'),
	);
});

return $container;