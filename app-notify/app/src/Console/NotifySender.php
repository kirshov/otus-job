<?php

declare(strict_types=1);

namespace App\Console;

use App\Repository\NotifyRepository;

class NotifySender
{
	protected NotifyRepository $repository;

	public function __construct(NotifyRepository $repository)
	{
		$this->repository = $repository;
	}

	public function __invoke()
	{
		foreach ($this->repository->getNotSendItems() as $item) {
			$this->repository->setDone($item['id']);
		}
	}
}