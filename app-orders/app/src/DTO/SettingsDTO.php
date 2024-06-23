<?php

namespace App\DTO;

class SettingsDTO
{
	public function __construct(
		public readonly string $storeUrl,
		public readonly string $billingUrl,
		public readonly string $notifyUrl,
		public readonly string $redisHost,
		public readonly string $redisPort,
		public readonly string $redisPass,
		public readonly string $rabbitHost,
		public readonly string $rabbitPort,
		public readonly string $rabbitUser,
		public readonly string $rabbitPass,
	)
	{
	}
}