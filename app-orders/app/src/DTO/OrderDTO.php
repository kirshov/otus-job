<?php
namespace App\DTO;

use App\Enum\StatusEnum;

class OrderDTO
{
	/**
	 * @param int $id
	 * @param int $userId
	 * @param StatusEnum $status
	 * @param int $total
	 * @param OrderProductDTO[] $products
	 */
	public function __construct(
		public int $id,
		public int $userId,
		public StatusEnum $status,
		public int $total,
		public array $products
	) {
	}

	public function asArray(): array
	{
		$productsAsArray = [];

		foreach ($this->products as $product) {
			$productsAsArray = $product->asArray();
		}

		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'status' => $this->status->value,
			'total' => $this->total,
			'products' => $productsAsArray,
		];
	}
}