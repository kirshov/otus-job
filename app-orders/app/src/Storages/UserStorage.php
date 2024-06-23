<?php

declare(strict_types=1);

namespace App\Storages;

class UserStorage
{
	private static ?int $id = null;
	private static ?string $email = null;
	private static bool $isAdmin = false;

	public static function getId(): ?int
	{
		return self::$id;
	}

	public static function setId(?int $id): void
	{
		self::$id = $id;
	}

	public static function getEmail(): ?string
	{
		return self::$email;
	}

	public static function setEmail(?string $email): void
	{
		self::$email = $email;
	}

	public static function setIsAdmin(bool $isAdmin): void
	{
		self::$isAdmin = $isAdmin;
	}

	public static function getIsAdmin(): bool
	{
		return self::$isAdmin;
	}
}