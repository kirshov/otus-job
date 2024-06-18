<?php

declare(strict_types=1);

namespace App\DTO;

class UserLogin
{
	public function __construct(
		public ?string $email,
		public ?string $password,
	) {}
}