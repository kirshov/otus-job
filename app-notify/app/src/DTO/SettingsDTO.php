<?php

namespace App\DTO;

class SettingsDTO
{
	public function __construct(
		public readonly string $rabbitHost,
		public readonly string $rabbitPort,
		public readonly string $rabbitUser,
		public readonly string $rabbitPass,
	)
	{
	}
}