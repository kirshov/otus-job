<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\UserSignin;
use App\Enum\StatusEnum;
use PDO;

class UserRepository
{
	private const TABLE = 'users';

	public function __construct(
		private readonly PDO $db
	){}

	public function existByEmail(string $email): bool
	{
		$query = 'SELECT id FROM ' . self::TABLE . ' WHERE email = :email';
		$params = [
			':email' => $email,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$x = (int)$stmt->fetchColumn(0);

		return $x !== 0;
	}

	public function findByEmailAndPassword(string $email, string $password): ?array
	{
		$query = 'SELECT * FROM ' . self::TABLE . ' WHERE email = :email AND password = :password LIMIT 1';
		$params = [
			':email' => $email,
			':password' => $password,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		return $user ?: null;
	}

	public function createUser(UserSignin $user): ?int
	{
		$query = 'INSERT INTO ' . self::TABLE. ' (id, "email", "name", "password", status, role, create_time) 
			VALUES (nextval(\'users_seq\'), :email, :name, :password, :status, :role, NOW())';
		$params = [
			'email' => $user->email,
			'name' => $user->name,
			'password' => $user->password,
			'status' => StatusEnum::ACTIVE->value,
			'role' => $user->role->value,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return (int)$this->db->lastInsertId() ?: null;
	}
}