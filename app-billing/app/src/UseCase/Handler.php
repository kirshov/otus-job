<?php

declare(strict_types=1);

namespace App\UseCase;

use App\DTO\OperationDTO;
use App\Enum\OperationTypeEnum;
use App\Repository\BillingRepository;
use InvalidArgumentException;

class Handler
{
	public function __construct(public readonly BillingRepository $billingRepository)
	{
	}

	public function handle(OperationDTO $operation): void
	{
		if ($operation->operationType == OperationTypeEnum::INCOMING) {
			if (0 === $operation->value && $this->billingRepository->isExist($operation->userId)) {
				return;
			}

			$this->billingRepository->change($operation->userId, $operation->value);
		} else {
			$balance = $this->billingRepository->getBalanceByUserId($operation->userId);

			if ($balance >= $operation->value) {
				$this->billingRepository->change($operation->userId, abs($operation->value) * -1);
			} else {
				throw new InvalidArgumentException('Не хватает средств');
			}
		}
	}
}