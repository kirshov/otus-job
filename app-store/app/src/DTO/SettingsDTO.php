<?php

namespace App\DTO;

class SettingsDTO
{
	public function __construct(
		public readonly string $redisHost,
		public readonly string $redisPort,
		public readonly string $redisPass,
	) {
	}
}