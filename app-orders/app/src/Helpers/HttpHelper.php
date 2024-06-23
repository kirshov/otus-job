<?php

declare(strict_types=1);

namespace App\Helpers;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HttpHelper
{
	/**
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws ClientExceptionInterface
	 */
	public static function request(string $method, string $url, array $data): array
	{
		$client = HttpClient::create();

		return $client->request($method, $url, $data)->toArray();
	}
}