<?php

declare(strict_types=1);

namespace App\Consumers;

interface IConsumer
{
	public function handle(array $data): void;
}