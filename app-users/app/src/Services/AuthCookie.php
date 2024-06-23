<?php

namespace App\Services;

class AuthCookie implements IAuthStorage
{
	public static function set(string $key, ?array $data): void
	{
		setcookie(
			$key,
			json_encode($data),
			0,
			"/"
		);
	}

	public static function get($key): ?array
	{
		if (!isset($_COOKIE[$key])) {
			return null;
		}

		return json_decode($_COOKIE[$key], true);
	}

	public static function remove($key): void
	{
		self::set($key, null);
	}
}