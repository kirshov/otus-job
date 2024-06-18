<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Services\AuthCookie;

class Logout
{
	public function run(): void
	{
		AuthCookie::remove();
	}
}