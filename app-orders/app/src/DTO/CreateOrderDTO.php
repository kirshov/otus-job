<?php
namespace App\DTO;

use App\Enum\StatusEnum;

class CreateOrderDTO
{
	/**
	 * @param int $userId
	 * @param StatusEnum $status
	 * @param OrderProductDTO[] $products
	 */
	public function __construct(
		public int $userId,
		public StatusEnum $status,
		public array $products,
		public ?string $idempotencyKey = null
	) {
	}
}