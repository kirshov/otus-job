<?php

declare(strict_types=1);

namespace App\UseCase\Order;

use App\DTO\CreateOrderDTO;
use App\DTO\OrderDTO;
use App\DTO\SettingsDTO;
use App\Enum\StatusEnum;
use App\Exceptions\BillingException;
use App\Exceptions\ReserveException;
use App\Repository\LogsRepository;
use App\Repository\OrdersRepository;
use App\Services\RabbitService;
use App\Storages\UserStorage;
use App\UseCase\Order\Services\NotifyService;
use App\UseCase\Order\Services\PaymentService;
use App\UseCase\Order\Services\ReserveService;
use Throwable;

class CreateAndPay
{
	private readonly ReserveService $reserveService;
	private readonly PaymentService $paymentService;
	private readonly NotifyService $notifyService;

	public function __construct(
		private readonly OrdersRepository $repository,
		private readonly LogsRepository $logsRepository,
		private readonly RabbitService $rabbitService,
		private readonly SettingsDTO $settings,
	) {
		$this->reserveService = new ReserveService($this->settings);
		$this->paymentService = new PaymentService($this->settings);
		$this->notifyService = new NotifyService($this->rabbitService);
	}

	/**
	 * @throws Throwable
	 */
	public function run(CreateOrderDTO $createOrder): OrderDTO
	{
		$issetOrder = null;

		if (null !== $createOrder->idempotencyKey) {
			$issetOrder = $this->repository->getByIdempotencyKey($createOrder->idempotencyKey);

			if (!empty($issetOrder) && $issetOrder->status === StatusEnum::PAID) {
				return $issetOrder;
			}
		}

		if (null !== $issetOrder) {
			$order = $issetOrder;
		} else {
			$orderId = $this->repository->create($createOrder);
			$order = $this->repository->getById($orderId);
		}

		$email = UserStorage::getEmail();

		try {
			//Резерв
			$this->reserveService->run($order);
			$this->logsRepository->add($order->userId, $order->id, 'Товары взяты в резерв');

			//Оплата
			$this->paymentService->run($order);
			$this->logsRepository->add($order->userId, $order->id, 'Деньги списаны со счета');

			//Нотификация
			$this->notifyService->sendSuccess($order, $email);
			$this->logsRepository->add($order->userId, $order->id, 'Уведомление success отправлено');
		} catch (ReserveException $exception) {
			//Ошибка резерва
			$this->logsRepository->add($order->userId, $order->id, 'Не удалось зарезервировать товары: ' . $exception->getMessage());
			$this->notifyService->sendFailure($order, $email, $exception->getMessage());
			$this->logsRepository->add($order->userId, $order->id, 'Уведомление failure отправлено');

			throw $exception;
		} catch (BillingException $exception) {
			//Снятие с резерва
			$this->logsRepository->add($order->userId, $order->id, 'Ошибка биллинга: ' . $exception->getMessage());
			$this->reserveService->compensation($order);
			$this->logsRepository->add($order->userId, $order->id, 'Товары сняты с резерва');

			$this->notifyService->sendFailure($order, $email, $exception->getMessage());
			$this->logsRepository->add($order->userId, $order->id, 'Уведомление failure отправлено');

			throw $exception;
		} catch (Throwable $exception) {
			//Общая ошибка
			$this->notifyService->sendFailure($order, $email, $exception->getMessage());
			$this->logsRepository->add($order->userId, $order->id, 'Возникла ошибка: ' . $exception->getMessage());

			throw $exception;
		}

		return $order;
	}
}