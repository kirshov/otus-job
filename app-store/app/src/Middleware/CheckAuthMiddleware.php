<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Storages\UserStorage;
use Predis\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Throwable;

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
			$user = null;

			try {
				if (null !== $token) {
					$userData = $this->redis->get($token);
					$user = json_decode($userData, true);
				}
			} catch (Throwable $throwable) {

			}

			if (!empty($user)) {
				UserStorage::setId((int)$user['id']);
				UserStorage::setEmail($user['email']);
				UserStorage::setIsAdmin((bool)$user['isAdmin']);
				UserStorage::setToken($token);
			}
		}

		return $handler->handle($request);
	}
}