<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CreateProductDTO;
use App\DTO\ProductDTO;
use App\DTO\UpdateProductDTO;
use App\Enum\StatusEnum;
use Exception;
use PDO;
use Throwable;

class ProductRepository
{
	private const TABLE = 'products';
	private const PER_PAGE = 20;

	public function __construct(
		private readonly PDO $db
	){}

	/**
	 * @throws Exception
	 */
	public function getById(int $id): ProductDTO
	{
		$query = 'SELECT * FROM ' . self::TABLE . ' WHERE id = :id AND status = :status';
		$params = [
			':id' => $id,
			':status' => StatusEnum::ACTIVE->value
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		$product = $stmt->fetch(PDO::FETCH_ASSOC);

		if (empty($product)) {
			throw new Exception('Товар не найден');
		}

		return new ProductDTO(
			$product['id'],
			$product['name'],
			(int)$product['price'],
			(int)$product['quantity'],
		);
	}

	public function create(CreateProductDTO $product): ?int
	{
		$query = 'INSERT INTO ' . self::TABLE. ' (id, "name", "price", "quantity", status, create_time) 
			VALUES (nextval(\'products_seq\'), :name, :price, :quantity, :status, NOW())';
		$params = [
			'name' => $product->name,
			'price' => $product->price,
			'quantity' => $product->quantity,
			'status' => StatusEnum::ACTIVE->value,
		];
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);

		return (int)$this->db->lastInsertId() ?: null;
	}

	public function update(UpdateProductDTO $product): void
	{
		$params = $update = [];

		if (null !== $product->name) {
			$params['name'] = $product->name;
		}
		if (null !== $product->price) {
			$params['price'] = $product->price;
		}
		if (null !== $product->quantity) {
			$params['quantity'] = $product->quantity;
		}

		foreach (array_keys($params) as $field) {
			$update[] = $field .' = :' . $field;
		}

		$query = 'UPDATE ' . self::TABLE. ' SET ' . implode(', ', $update) . ' WHERE id = :id';
		$stmt = $this->db->prepare($query);
		$stmt->execute(array_merge($params, ['id' => $product->id]));
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

		$productsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$result = [];

		foreach ($productsRaw as $productItem) {
			$result[] = (new ProductDTO(
				$productItem['id'],
				$productItem['name'],
				(int)$productItem['price'],
				(int)$productItem['quantity'],
			))->asArray();
		}

		return $result;
	}

	/**
	 * @var UpdateProductDTO[] $products
	 * @throws Throwable
	 */
	public function reserve(array $products): void
	{
		$this->db->beginTransaction();

		try {
			foreach ($products as $product) {
				$selectQuery = 'SELECT * FROM ' . self::TABLE . ' WHERE id = :id AND status = :status FOR UPDATE';

				$params = [
					':id' => $product->id,
					':status' => StatusEnum::ACTIVE->value
				];
				$stmt = $this->db->prepare($selectQuery);
				$stmt->execute($params);

				$productInDb = $stmt->fetch(PDO::FETCH_ASSOC);

				if (empty($productInDb)) {
					throw new Exception('Товар не найден');
				}

				if ($product->quantity === 0) {
					throw new Exception('Необходимо указать количество для "' . $productInDb['name'] . '"');
				}

				if ((int)$productInDb['quantity'] < $product->quantity) {
					throw new Exception('Не хватает количества для "' . $productInDb['name'] . '". Доступно: ' . $productInDb['quantity']);
				}

				$query = 'UPDATE ' . self::TABLE. ' SET quantity = quantity - ' . $product->quantity . ' WHERE id = :id';
				$stmt = $this->db->prepare($query);
				$stmt->execute(array_merge(['id' => $product->id]));
			}

			$this->db->commit();
		} catch (Throwable $throwable) {
			$this->db->rollBack();

			throw $throwable;
		}
	}
}