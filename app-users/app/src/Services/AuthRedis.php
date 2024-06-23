<?php

namespace App\Services;

use Predis\Client;

class AuthRedis implements IAuthStorage
{
	static ?Client $redis = null;

	public function __construct(Client $redis)
	{
		self::$redis = $redis;
	}

	public static function set(string $key, ?array $data):void
	{
		self::$redis->set($key, json_encode($data));
	}

	public static function get($key): ?array
	{
		$data = self::$redis->get($key);

		if (null === $data) {
			return null;
		}

		return json_decode($data, true);
	}

	public static function remove($key): void
	{
		self::$redis->del($key);
	}
}