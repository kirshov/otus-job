<?php

declare(strict_types=1);

namespace App\Services;

class PasswordHash
{
	public static function hash(string $password):string
	{
		return sha1($password);
	}
}