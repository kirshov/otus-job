<?php

declare(strict_types=1);

namespace App\UseCase\Product;

use App\DTO\UpdateProductDTO;
use App\Repository\ProductRepository;

class Update
{
	public function __construct(
		private readonly ProductRepository $repository,
	) {}

	public function run(UpdateProductDTO $product): void
	{
		$this->repository->update($product);
	}
}