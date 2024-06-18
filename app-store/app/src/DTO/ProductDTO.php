<?php

namespace App\DTO;

class ProductDTO
{
	public function __construct(
		public int $id,
		public string $name,
		public int $price,
		public int $quantity
	) {
	}

	public function asArray()
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'price' => $this->price,
			'quantity' => $this->quantity,
		];
	}
}