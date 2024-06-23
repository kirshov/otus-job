<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class BillingRepository
{
	private const TABLE = 'billing';

	public function __construct(
		private readonly PDO $db
	){}

	public function getBalanceByUserId(int $id): int
	{
		$query = 'SELECT SUM(value) FROM ' . self::TABLE . ' WHERE user_id = :userId';
		$params = [
			':userId' => $id,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$balance = $stmt->fetch(PDO::FETCH_COLUMN);

		return (int)$balance;
	}

	public function change(int $userId, int $value): ?int
	{
		$query = 'INSERT INTO ' . self::TABLE. ' (id, user_id, "value", create_time) 
			VALUES (nextval(\'' . self::TABLE. '_seq\'), :userId, :value, NOW())';
		$params = [
			':userId' => $userId,
			':value' => $value,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return (int)$this->db->lastInsertId() ?: null;
	}


	public function isExist(int $userId): bool
	{
		$query = 'SELECT id 
			FROM ' . self::TABLE. ' 
			WHERE user_id = :userId
			LIMIT 1';
		$params = [
			':userId' => $userId,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return $stmt->fetch(PDO::FETCH_COLUMN) > 0;
	}
}