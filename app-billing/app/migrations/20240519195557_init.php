<?php

declare(strict_types=1);

use Phoenix\Exception\InvalidArgumentValueException;
use Phoenix\Migration\AbstractMigration;

final class Init extends AbstractMigration
{
	private const TABLE = 'billing';
	/**
	 * @throws InvalidArgumentValueException
	 */
	protected function up(): void
	{
		$this->table('billing')
			->addColumn('id', 'biginteger')
			->addColumn('user_id', 'integer')
			->addColumn('value', 'integer')
			->addColumn('create_time', 'datetime')
			->create();

		$this->execute('CREATE SEQUENCE ' . self::TABLE . '_seq INCREMENT BY 1 MINVALUE 1 START 1');

	}

	protected function down(): void {
		$this->delete(self::TABLE);
		$this->execute('DROP SEQUENCE ' . self::TABLE . '_seq');
	}
}