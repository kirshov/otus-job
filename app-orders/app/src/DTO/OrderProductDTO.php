<?php
namespace App\DTO;

class OrderProductDTO
{
	public function __construct(
		public int $id,
		public string $name,
		public int $price,
		public int $quantity
	) {
	}

	public function asArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'price' => $this->price,
			'quantity' => $this->quantity,
		];
	}
}