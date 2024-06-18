<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Storages\UserStorage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CheckAuthMiddleware implements MiddlewareInterface
{
	public function process(Request $request, RequestHandler $handler): Response
	{
		$skipRequest = ['incoming'];

		if (!in_array(trim($request->getRequestTarget(), '/'), $skipRequest)) {
			$userId = $request->getHeader('X-UserId')[0] ?? null;

			if (null === $userId) {
				$result = [
					'status' => 'error',
					'error' => 'Пользователь не определен',
				];

				echo json_encode($result, JSON_UNESCAPED_UNICODE);
				exit();
			}

			UserStorage::setUserId((int)$userId);
		}

		return $handler->handle($request);
	}
}