<?php

declare(strict_types=1);

namespace App\DTO;

class QueueDTO
{
	public function __construct(
		public readonly int $userId,
		public readonly string $email,
		public readonly string $text,
	) {
	}
}