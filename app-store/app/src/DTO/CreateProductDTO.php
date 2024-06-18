<?php
namespace App\DTO;

class CreateProductDTO
{
	public function __construct(
		public string $name,
		public int $price,
		public int $quantity
	) {
	}
}