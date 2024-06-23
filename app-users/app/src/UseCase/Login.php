<?php

declare(strict_types=1);

namespace App\UseCase;

use App\DTO\UserLogin;
use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use App\Services\AuthRedis;
use App\Services\IAuthStorage;
use App\Services\PasswordHash;

class Login
{
	public function __construct(
		private readonly UserRepository $repository,
		private readonly IAuthStorage $authRedis,
	) {}

	public function run(UserLogin $user): void
	{
		$result = $this->repository->findByEmailAndPassword($user->email, PasswordHash::hash($user->password));

		if (null === $result) {
			throw new \RuntimeException('Неверный логин или пароль');
		}

		AuthRedis::set(session_id(), [
			'id' => $result['id'],
			'name' => $result['name'],
			'email' => $result['email'],
			'isAdmin' => ((int)$result['role'] === RoleEnum::ADMIN->value),
		]);
	}
}