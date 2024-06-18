<?php

declare(strict_types=1);

namespace App\UseCase;

use App\DTO\UserLogin;
use App\Repository\UserRepository;
use App\Services\AuthCookie;
use App\Services\PasswordHash;

class Login
{
	public function __construct(
		private readonly UserRepository $repository,
	) {}

	public function run(UserLogin $user): void
	{
		$result = $this->repository->findByEmailAndPassword($user->email, PasswordHash::hash($user->password));

		if (null === $result) {
			throw new \RuntimeException('Неверный логин или пароль');
		}

		AuthCookie::set([
			'id' => $result['id'],
			'name' => $result['name'],
			'email' => $result['email'],
		]);
	}
}