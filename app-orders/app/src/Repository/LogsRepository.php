<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class LogsRepository
{
	private const TABLE = 'event_log';
	private const PER_PAGE = 50;

	public function __construct(
		private readonly PDO $db
	){}

	public function add(int $userId, int $orderId, string $message): ?int
	{
		$query = 'INSERT INTO ' . self::TABLE. ' (id, user_id, order_id, message, create_time) 
			VALUES (nextval(\'' . self::TABLE . '_seq\'), :user_id, :order_id, :message, NOW())';
		$params = [
			'user_id' => $userId,
			'order_id' => $orderId,
			'message' => $message,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return (int)$this->db->lastInsertId() ?: null;
	}

	public function getAll(?int $page): array
	{
		$query = 'SELECT * FROM ' . self::TABLE . ' ORDER BY create_time DESC LIMIT ' . self::PER_PAGE;
		$offset = 0;

		if (is_numeric($page) && $page > 1) {
			$offset = ($page - 1) * self::PER_PAGE;
		}

		$query .= ' OFFSET ' . $offset;

		$stmt = $this->db->prepare($query);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}