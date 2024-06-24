<?php

use App\Console\NotifySender;
use App\Enum\DbOrderEnum;
use App\Middleware\AfterMiddleware;
use App\Middleware\JsonBodyParserMiddleware;
use App\Repository\NotifyRepository;
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

$app->add(new AfterMiddleware());
$app->add(new JsonBodyParserMiddleware());

$app->post('/add',function (Request $request, Response $response): Response
{
	$params = (array)$request->getParsedBody();

	try {
		/** @var NotifyRepository $notifyRepository */
		$notifyRepository = $this->get('notifyRepository');
		$id = $notifyRepository->add($params['userId'], $params['email'], $params['text']);

		$sender = new NotifySender($notifyRepository);
		$sender();

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

$app->get('/all',function (Request $request, Response $response): Response
{
	try {
		/** @var NotifyRepository $notifyRepository */
		$notifyRepository = $this->get('notifyRepository');

		$result = [
			'status' => 'success',
			'items' => $notifyRepository->getItems(),
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

$app->get('/get',function (Request $request, Response $response): Response
{
	$params = $request->getQueryParams();

	try {
		/** @var NotifyRepository $notifyRepository */
		$notifyRepository = $this->get('notifyRepository');

		$result = [
			'status' => 'success',
			'data' => $notifyRepository->getItemsById($params['id']),
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


$app->get('/get-last',function (Request $request, Response $response): Response
{
	try {
		/** @var NotifyRepository $notifyRepository */
		$notifyRepository = $this->get('notifyRepository');

		$result = [
			'status' => 'success',
			'data' => $notifyRepository->getLast(),
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