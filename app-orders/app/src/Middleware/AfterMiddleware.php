<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AfterMiddleware implements MiddlewareInterface
{
	public function process(Request $request, RequestHandler $handler): Response
	{
		$response = $handler->handle($request);
		$response = $response->withAddedHeader('Content-Type', 'application/json');

		return $response;
	}
}