<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Services\AuthCookie;

class Auth
{
	public function run(): ?array
	{
		$data = AuthCookie::get();

		if (null === $data) {
			return null;
		}

		return [
			'X-UserId' => $data['id'],
			'X-Email' => $data['email'],
			'X-Name' => $data['name'],
		];
	}
}