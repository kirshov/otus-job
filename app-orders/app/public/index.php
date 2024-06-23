<?php

use App\DTO\CreateOrderDTO;
use App\DTO\OrderProductDTO;
use App\Enum\StatusEnum;
use App\Middleware\AfterMiddleware;
use App\Middleware\CheckAuthMiddleware;
use App\Middleware\JsonBodyParserMiddleware;
use App\Repository\OrdersRepository;
use App\Storages\UserStorage;
use App\UseCase\Order\CreateAndPay;
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

$app->add(new CheckAuthMiddleware($container->get('redis')));
$app->add(new JsonBodyParserMiddleware());
$app->add(new AfterMiddleware());

$app->get('/list', function (Request $request, Response $response) {
	$params = $request->getQueryParams();

	try {
		$page = (isset($params['page'])) ? (int)$params['page'] : null;

		/** @var OrdersRepository $repository */
		$repository = $this->get('ordersRepository');
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
	$idempotencyKey = $request->getHeader('X-Idempotency-Key')[0] ?? null;

	try {
		$handler = new CreateAndPay(
			$this->get('ordersRepository'),
			$this->get('logsRepository'),
			$this->get('rabbitService'),
			$this->get('settings'),
		);
		$products = [];

		foreach ((array)$params['items'] as $item) {
			$products[] = new OrderProductDTO(
				(int)$item['id'],
				$item['name'],
				(int)$item['price'],
				(int)$item['quantity'],
			);
		}

		$order = new CreateOrderDTO(
			userId: UserStorage::getId(),
			status: StatusEnum::ACTIVE,
			products: $products,
			idempotencyKey: $idempotencyKey
		);

		$resultOrder = $handler->run($order);

		$result = [
			'status' => 'success',
			'data' => $resultOrder->asArray(),
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