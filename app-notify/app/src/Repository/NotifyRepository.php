<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\DbOrderEnum;
use App\Enum\StatusEnum;
use PDO;

class NotifyRepository
{
	private const TABLE = 'notify_queue';

	public function __construct(
		private readonly PDO $db
	){}

	public function add(int $userId, string $email, string $text): ?int
	{
		$query = 'INSERT INTO ' . self::TABLE. ' (id, user_id, email, text, status, create_time) 
			VALUES (nextval(\'notify_queue_seq\'), :userId, :email, :text, :status, NOW())';
		$params = [
			':userId' => $userId,
			':email' => $email,
			':text' => $text,
			':status' => StatusEnum::WAIT->value
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return (int)$this->db->lastInsertId() ?: null;
	}

	public function getNotSendItems(int $limit = 10): array
	{
		$query = 'SELECT * 
			FROM ' . self::TABLE. ' 
			WHERE status = :statusId
			ORDER BY id ASC
			LIMIT ' . $limit;

		$params = [
			':statusId' => StatusEnum::WAIT->value,
		];

		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getItems(int $limit = 10, DbOrderEnum $orderEnum = DbOrderEnum::ASC): array
	{
		$query = 'SELECT * 
			FROM ' . self::TABLE. ' 
			ORDER BY id ' . $orderEnum->value . '
			LIMIT ' . $limit;

		$stmt = $this->db->prepare($query);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getItemsById(int $id): array
	{
		$query = 'SELECT * 
			FROM ' . self::TABLE. ' 
			WHERE id = :id';

		$params = [
			':id' => $id
		];

		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


	public function getLast(): array
	{
		$query = 'SELECT * 
			FROM ' . self::TABLE. '
			ORDER BY id DESC 
			LIMIT 1';

		$stmt = $this->db->prepare($query);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function setDone(int $id): void
	{
		$query = 'UPDATE ' . self::TABLE. ' 
			SET status = :statusId
			WHERE id = :id';

		$params = [
			':statusId' => StatusEnum::DONE->value,
			':id' => $id,
		];

		$stmt = $this->db->prepare($query);
		$stmt->execute($params);
	}
}