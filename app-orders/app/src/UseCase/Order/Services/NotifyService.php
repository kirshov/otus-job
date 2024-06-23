<?php

declare(strict_types=1);

namespace App\UseCase\Order\Services;

use App\DTO\OrderDTO;
use App\Services\RabbitService;

class NotifyService
{
	private const RABBIT_QUEUE = 'notification';

	public function __construct(
		private readonly RabbitService $rabbit
	) {
	}

	public function sendSuccess(OrderDTO $order, string $email): void
	{
		$message = sprintf('Ваш заказ %d успешно оплачен!', $order->id);

		$this->rabbit->send(self::RABBIT_QUEUE, [
			'userId' => $order->userId,
			'email' => $email,
			'message' => $message
		]);
	}

	public function sendFailure(OrderDTO $order, string $email, string $error): void
	{
		$message = sprintf('Ваш заказ %d НЕ оплачен! Причина: %s', $order->id, $error);

		$this->rabbit->send(self::RABBIT_QUEUE, [
			'userId' => $order->userId,
			'email' => $email,
			'message' => $message
		]);
	}
}