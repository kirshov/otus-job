<?php

declare(strict_types=1);

namespace App\UseCase;

use App\DTO\UserSignin;
use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use App\Services\PasswordHash;
use RuntimeException;

class Registration
{
	public function __construct(
		private readonly UserRepository $repository,
	) {}

	public function run(UserSignin $user): ?int
	{
		$this->checkFields($user);

		if ($this->repository->existByEmail($user->email)) {
			throw new RuntimeException('Пользователь с таким email уже существует');
		}

		$user->password = PasswordHash::hash($user->password);
		$user->role = RoleEnum::USER;

		return $this->repository->createUser($user);
	}

	private function checkFields(UserSignin $user): void
	{
		if (!$this->checkField($user->email)) {
			throw new RuntimeException('Не заполнен email');
		}

		if (!$this->checkField($user->name)) {
			throw new RuntimeException('Не заполнено имя');
		}

		if (!$this->checkField($user->password)) {
			throw new RuntimeException('Не заполнен пароль');
		}
	}

	private function checkField(string $value): bool
	{
		return !empty($value);
	}
}