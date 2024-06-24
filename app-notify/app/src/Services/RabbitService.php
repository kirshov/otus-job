<?php

declare(strict_types=1);

namespace App\Services;

use App\Consumers\IConsumer;
use App\DTO\SettingsDTO;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Throwable;

class RabbitService
{
	public const NOTIFY_QUEUE = 'notification';

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

	public function receive(string $route, IConsumer $handler): void
	{
		$this->channel->queue_declare($route, false, false, false, false);

		$callback = function ($msg) use ($handler) {
			try {
				$data = json_decode($msg->getBody(), true);
				$handler->handle($data);
				$msg->ack();
			} catch (Throwable $throwable) {
				var_dump($throwable->getTraceAsString());
			}
		};

		$this->channel->basic_qos(null, 1, false);
		$this->channel->basic_consume(self::NOTIFY_QUEUE, '', false, false, false, false, $callback);

		while ($this->channel->is_consuming()) {
			$this->channel->wait();
		}
	}
}