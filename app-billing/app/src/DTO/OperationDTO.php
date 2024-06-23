<?php

declare(strict_types=1);

namespace App\DTO;
use App\Enum\OperationTypeEnum;

class OperationDTO
{
	public function __construct(
		public readonly OperationTypeEnum $operationType,
		public readonly int $userId,
		public readonly int $value,
	) {
	}
}