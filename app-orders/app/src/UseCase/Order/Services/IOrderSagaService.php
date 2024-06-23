<?php

namespace App\UseCase\Order\Services;

use App\DTO\OrderDTO;

interface IOrderSagaService
{
	public function run(OrderDTO $order): void;
	public function compensation(OrderDTO $order): void;
}