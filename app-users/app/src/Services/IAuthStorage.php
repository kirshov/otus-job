<?php

namespace App\Services;

interface IAuthStorage
{
	public static function set(string $key, ?array $data): void;
	public static function get($key): ?array;
	public static function remove($key): void;
}