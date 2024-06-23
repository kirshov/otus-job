<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\SettingsDTO;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitService
{
	private AMQPChannel $channel;

	public function __construct(
		private readonly SettingsDTO $settings
	) {
		$connection = new AMQPStreamConnection(
			$this->settings->rabbitHost,
			$this->settings->rabbitPort,
			$this->settings->rabbitUser,
			$this->settings->rabbitPass
		);
		$this->channel = $connection->channel();
	}

	public function send(string $route, array $data): void
	{
		$this->channel->queue_declare($route, false, false, false, false);

		$msg = new AMQPMessage(json_encode($data));
		$this->channel->basic_publish($msg, '', $route);
	}
}