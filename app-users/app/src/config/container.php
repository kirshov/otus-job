<?php
/**
 * @var ContainerInterface $container
 */

use App\Repository\UserRepository;
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

return $container;