<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Storages\UserStorage;
use Predis\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CheckAuthMiddleware implements MiddlewareInterface
{
	public function __construct(
		private readonly Client $redis
	) {
	}

	public function process(Request $request, RequestHandler $handler): Response
	{
		$skipRequest = [];

		if (!in_array(trim($request->getRequestTarget(), '/'), $skipRequest)) {
			$token = $request->getHeader('X-Token')[0] ?? null;

			if (null !== $token) {
				$userData = $this->redis->get($token);
				$user = json_decode($userData, true);
			}

			if (empty($user)) {
				$result = [
					'status' => 'error',
					'error' => 'Пользователь не определен',
				];

				echo json_encode($result, JSON_UNESCAPED_UNICODE);
				exit();
			}

			UserStorage::setId((int)$user['id']);
			UserStorage::setEmail($user['email']);
		}

		return $handler->handle($request);
	}
}