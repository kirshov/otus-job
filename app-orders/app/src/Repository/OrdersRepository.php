<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CreateOrderDTO;
use App\DTO\OrderDTO;
use App\DTO\OrderProductDTO;
use App\Enum\StatusEnum;
use Exception;
use PDO;
use Throwable;

class OrdersRepository
{
	protected const TABLE = 'orders';
	protected const PRODUCT_TABLE = 'orders_product';
	private const PER_PAGE = 5;

	public function __construct(
		private readonly PDO $db
	){}

	public function getById(int $id): OrderDTO
	{
		$query = 'SELECT * FROM ' . self::TABLE . ' WHERE id = :id';
		$params = [
			':id' => $id,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$order = $stmt->fetch(PDO::FETCH_ASSOC);

		if (empty($order)) {
			throw new Exception('Не удалось найти заказ');
		}

		return new OrderDTO(
			$order['id'],
			$order['user_id'],
			StatusEnum::from($order['status']),
			(int)$order['total'],
			$this->getProductsByOrderId($order['id'])
		);
	}

	public function getByIdempotencyKey(string $idempotencyKey): ?OrderDTO
	{
		$query = 'SELECT * FROM ' . self::TABLE . ' WHERE idempotency_key = :idempotency_key';
		$params = [
			':idempotency_key' => $idempotencyKey,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$order = $stmt->fetch(PDO::FETCH_ASSOC);

		return !empty($order)
			? new OrderDTO(
				$order['id'],
				$order['user_id'],
				StatusEnum::from($order['status']),
				(int)$order['total'],
				$this->getProductsByOrderId($order['id'])
			)
			: null;
	}

	protected function getProductsByOrderId(int $orderId): array
	{
		$query = 'SELECT * FROM ' . self::PRODUCT_TABLE . ' WHERE order_id = :id';
		$params = [
			':id' => $orderId,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$result = [];

		foreach ($products as $product) {
			$result[] = new OrderProductDTO(
				id: $product['product_id'],
				name: $product['name'],
				price: $product['price'],
				quantity: $product['quantity'],
			);
		}

		return $result;
	}

	public function create(CreateOrderDTO $order): int
	{
		$this->db->beginTransaction();

		try {
			$total = 0;

			foreach ($order->products as $product) {
				$total += $product->price * $product->quantity;
			}

			$query = 'INSERT INTO ' . self::TABLE. ' (id, user_id, status, total, idempotency_key, create_time) 
				VALUES (nextval(\'' . self::TABLE . '_seq\'), :user_id, :status, :total, :idempotency_key, NOW())';
			$params = [
				'user_id' => $order->userId,
				'status' => StatusEnum::ACTIVE->value,
				'total' => $total,
				'idempotency_key' => $order->idempotencyKey,
			];
			$stmt = $this->db->prepare($query);
			$stmt->execute($params);

			$id = (int)$this->db->lastInsertId() ?: null;

			foreach ($order->products as $product) {
				$query = 'INSERT INTO ' . self::PRODUCT_TABLE. ' (id, order_id, product_id, name, quantity, price) 
				VALUES (nextval(\'' . self::PRODUCT_TABLE . '_seq\'), :order_id, :product_id, :name, :quantity, :price)';
				$params = [
					'order_id' => $id,
					'product_id' => $product->id,
					'name' => $product->name,
					'quantity' => $product->quantity,
					'price' => $product->price,
				];
				$stmt = $this->db->prepare($query);
				$stmt->execute($params);
			}

			$this->db->commit();
		} catch (Throwable $throwable) {
			$this->db->rollBack();
			throw $throwable;
		}

		return $id;
	}

	public function getAll(?int $page): array
	{
		$query = 'SELECT * FROM ' . self::TABLE . ' WHERE status = :status ORDER BY create_time DESC LIMIT ' . self::PER_PAGE;
		$offset = 0;

		if (is_numeric($page) && $page > 1) {
			$offset = ($page - 1) * self::PER_PAGE;
		}

		$query .= ' OFFSET ' . $offset;
		$params = [
			':status' => StatusEnum::ACTIVE->value
		];

		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$ordersRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$result = [];

		foreach ($ordersRaw as $order) {
			$result[] = new OrderDTO(
				$order['id'],
				$order['user_id'],
				StatusEnum::from($order['status']),
				(int)$order['total'],
				$this->getProductsByOrderId($order['id'])
			);
		}

		return $result;
	}
}