<?php
namespace App\DTO;

class UpdateProductDTO
{
	public function __construct(
		public int $id,
		public ?string $name = null,
		public ?int $price = null,
		public ?int $quantity = null,
	) {
	}
}