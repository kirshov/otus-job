<?php

use App\DTO\OperationDTO;
use App\Middleware\AfterMiddleware;
use App\Middleware\CheckAuthMiddleware;
use App\Middleware\JsonBodyParserMiddleware;
use App\Enum\OperationTypeEnum;
use App\Storages\UserStorage;
use App\UseCase\Handler;
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

$app->get('/get', function (Request $request, Response $response): Response
{
	$result = [
		'balance' => $this->get('billingRepository')->getBalanceByUserId(UserStorage::getId()),
	];

	$response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));

	return $response;
});


$app->post('/create',function (Request $request, Response $response): Response
{
	try {
		$operation = new OperationDTO(
			OperationTypeEnum::INCOMING,
			UserStorage::getId(),
			0
		);

		$handler = new Handler($this->get('billingRepository'));
		$handler->handle($operation);

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

$app->post('/incoming', function (Request $request, Response $response): Response
{
	$params = (array)$request->getParsedBody();

	try {
		$operation = new OperationDTO(
			OperationTypeEnum::INCOMING,
			$params['userId'],
			$params['value'] ?? 0
		);

		$handler = new Handler($this->get('billingRepository'));
		$handler->handle($operation);

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

$app->post('/pay', function (Request $request, Response $response): Response
{
	$params = (array)$request->getParsedBody();

	try {
		$operation = new OperationDTO(
			OperationTypeEnum::OUTCOMING,
			UserStorage::getId(),
			$params['value'] ?? 0
		);

		$handler = new Handler($this->get('billingRepository'));
		$handler->handle($operation);

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