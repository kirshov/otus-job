<?php

use App\DTO\CreateProductDTO;
use App\DTO\UpdateProductDTO;
use App\Middleware\AfterMiddleware;
use App\Middleware\JsonBodyParserMiddleware;
use App\Repository\ProductRepository;
use App\UseCase\Product\Create;
use App\UseCase\Product\Update;
use DI\Container;
use Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

require '../vendor/autoload.php';

$container = new Container();

AppFactory::setContainer($container);
$app = AppFactory::create();

/** @var ContainerInterface $container */
$container = include dirname(__DIR__) . '/src/config/container.php';

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->add(new JsonBodyParserMiddleware());
$app->add(new AfterMiddleware());

$app->get('/', function (Request $request, Response $response) {
	$params = $request->getQueryParams();

	try {
		$id = $params['id'];

		if (!is_numeric($id)) {
			throw new InvalidArgumentException('Не указан ID');
		}

		/** @var ProductRepository $repository */
		$repository = $this->get('productRepository');
		$product = $repository->getById((int)$id);

		$result = $product->asArray();
	} catch (Throwable $throwable) {
		$result = [
			'status' => 'error',
			'error' => $throwable->getMessage(),
		];
	}

	$response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));

	return $response;
});

$app->get('/list', function (Request $request, Response $response) {
	$params = $request->getQueryParams();

	try {
		$page = (isset($params['page'])) ? (int)$params['page'] : null;

		/** @var ProductRepository $repository */
		$repository = $this->get('productRepository');
		$result = $repository->getAll($page);
	} catch (Throwable $throwable) {
		$result = [
			'status' => 'error',
			'error' => $throwable->getMessage(),
		];
	}

	$response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));

	return $response;
});

$app->post('/create', function (Request $request, Response $response): Response
{
	$params = (array)$request->getParsedBody();

	$product = new CreateProductDTO(
		name: $params['name'],
		price: (int)$params['price'],
		quantity: (int)$params['quantity']
	);

	try {
		$handler = new Create($this->get('productRepository'));
		$id = $handler->run($product);

		$result = [
			'status' => 'success',
			'id' => $id,
		];
	} catch (Throwable $throwable) {
		$result = [
			'status' => 'error',
			'error' => $throwable->getMessage(),
		];
	}

	$response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));

	return $response;
});


$app->put('/update', function (Request $request, Response $response): Response
{
	$params = (array)$request->getParsedBody();

	$product = new UpdateProductDTO(
		id: $params['id'] ?? null,
		name: $params['name'] ?? null,
		price: $params['price'] ?? null,
		quantity: $params['quantity'] ?? null
	);

	try {
		$handler = new Update($this->get('productRepository'));
		$handler->run($product);

		$result = [
			'status' => 'success',
		];
	} catch (Throwable $throwable) {
		$result = [
			'status' => 'error',
			'error' => $throwable->getMessage(),
		];
	}

	$response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));

	return $response;
});

$app->run();