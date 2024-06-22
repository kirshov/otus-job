<?php

declare(strict_types=1);

namespace App\UseCase\Product;

use App\DTO\UpdateProductDTO;
use App\Repository\ProductRepository;
use Throwable;

class Reserve
{
	public function __construct(
		private readonly ProductRepository $repository,
	) {}

	/**
	 * @param UpdateProductDTO[] $products
	 * @return void
	 * @throws Throwable
	 */
	public function run(array $products): void
	{
		$this->repository->reserve($products);
	}
}