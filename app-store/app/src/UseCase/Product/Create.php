<?php

declare(strict_types=1);

namespace App\UseCase\Product;

use App\DTO\CreateProductDTO;
use App\Repository\ProductRepository;

class Create
{
	public function __construct(
		private readonly ProductRepository $repository,
	) {}

	public function run(CreateProductDTO $product): ?int
	{
		return $this->repository->create($product);
	}
}