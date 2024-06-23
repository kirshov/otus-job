<?php

declare(strict_types=1);

namespace App\UseCase\Order\Services;

use App\DTO\OrderDTO;
use App\DTO\SettingsDTO;
use App\Exceptions\BillingException;
use App\Helpers\HttpHelper;
use Exception;
use Throwable;

class PaymentService implements IOrderSagaService
{
	public function __construct(
		private readonly SettingsDTO $settings,
	) {
	}

	/**
	 * @throws Throwable
	 */
	public function run(OrderDTO $order): void
	{
		try {
			$response = HttpHelper::request('POST', $this->settings->billingUrl . '/pay', [
				'json' => [
					'value' => $order->total,
				],
				'headers' => [
					'X-UserId' => $order->userId,
				]
			]);

			if ($response['status'] !== 'success') {
				throw new BillingException($response['error']);
			}
		} catch (Throwable $throwable) {
			throw new BillingException($throwable->getMessage());
		}
	}

	public function compensation(OrderDTO $order): void
	{
	}
}