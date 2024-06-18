<?php

namespace App\Services;

class AuthCookie
{
	public const KEY = 'auth';

	public static function set(?array $data):void
	{
		setcookie(
			self::KEY,
			json_encode($data),
			0,
			"/"
		);
	}

	public static function get(): ?array
	{
		if (!isset($_COOKIE[self::KEY])) {
			return null;
		}

		return json_decode($_COOKIE[self::KEY], true);
	}

	public static function remove(): void
	{
		self::set(null);
	}
}