<?php

declare(strict_types=1);

namespace App\Consumers;

use App\Enum\StatusEnum;
use App\Repository\NotifyRepository;
use App\Services\RabbitService;

class NotifyConsumer implements IConsumer
{
	public function __construct(
		private readonly RabbitService $rabbitService,
		private readonly NotifyRepository $notifyRepository
	) {
	}

	public function __invoke(): void
	{
		$this->rabbitService->receive(RabbitService::NOTIFY_QUEUE, $this);
	}

	public function handle(array $data): void
	{
		$this->notifyRepository->add((int)$data['userId'], $data['email'], $data['message'], StatusEnum::DONE);
	}
}