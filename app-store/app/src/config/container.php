<?php
/**
 * @var ContainerInterface $container
 */

use App\Repository\ProductRepository;
use Psr\Container\ContainerInterface;

$container->set('db', function(){
	return new PDO(
		'pgsql:host=' . $_ENV['POSTGRES_HOST'] . ';dbname=' . $_ENV['POSTGRES_DB'],
		$_ENV['POSTGRES_USER'],
		$_ENV['POSTGRES_PASSWORD']
	);
});

$container->set('productRepository', function(ContainerInterface $container){
	return new ProductRepository($container->get('db'));
});

return $container;