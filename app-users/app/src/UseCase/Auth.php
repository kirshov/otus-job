<?php

declare(strict_types=1);

namespace App\UseCase;

class Auth
{
	public function run(): ?array
	{
		return [
			'X-Token' => session_id(),
		];
	}
}