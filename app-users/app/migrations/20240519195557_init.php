<?php

declare(strict_types=1);

use Phoenix\Exception\InvalidArgumentValueException;
use Phoenix\Migration\AbstractMigration;

final class Init extends AbstractMigration
{
	/**
	 * @throws InvalidArgumentValueException
	 */
	protected function up(): void
	{
		$this->table('users')
			->addColumn('id', 'biginteger')
			->addColumn('name', 'string')
			->addColumn('email', 'string')
			->addColumn('password', 'string')
			->addColumn('status', 'integer')
			->addColumn('role', 'integer')
			->addColumn('create_time', 'datetime')
			->create();

		$this->execute('CREATE SEQUENCE users_seq INCREMENT BY 1 MINVALUE 1 START 1');
	}

	protected function down(): void {
		$this->delete('users');
		$this->execute('DROP SEQUENCE users_seq');
	}
}