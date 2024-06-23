<?php

declare(strict_types=1);

namespace App\UseCase\Order\Services;

use App\DTO\OrderDTO;
use App\DTO\SettingsDTO;
use App\Helpers\HttpHelper;
use App\Storages\UserStorage;
use Exception;
use Throwable;

class ReserveService implements IOrderSagaService
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
		$response = $this->request($order, $this->getProductsArray($order));

		if ($response['status'] !== 'success') {
			throw new Exception($response['error']);
		}
	}

	/**
	 * @throws Throwable
	 */
	public function compensation(OrderDTO $order): void
	{
		$response = $this->request($order, $this->getProductsArray($order, -1));

		if ($response['status'] !== 'success') {
			throw new Exception($response['error']);
		}
	}

	/**
	 * @throws Throwable
	 */
	private function request(OrderDTO $order, array $data): array
	{
		return HttpHelper::request('POST', $this->settings->storeUrl . '/reserve', [
			'json' => [
				'items' => $data,
			],
			'headers' => [
				'X-UserId' => $order->userId,
				'X-Token' => UserStorage::getToken(),
			]
		]);
	}

	private function getProductsArray(OrderDTO $order, int $k = 1): array
	{
		$data = [];

		foreach ($order->products as $product) {
			$product->quantity *= $k;
			$data[] = $product->asArray();
		}

		return $data;
	}
}