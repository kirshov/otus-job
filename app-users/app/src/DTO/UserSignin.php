<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\RoleEnum;

class UserSignin
{
	public function __construct(
		public ?string $email,
		public ?string $name,
		public ?string $password,
		public RoleEnum $role
	) {}
}