<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Services\IAuthStorage;

class Logout
{
	public function __construct(
		private readonly IAuthStorage $authRedis,
	) {
	}

	public function run(): void
	{
		$this->authRedis::remove(session_id());
	}
}