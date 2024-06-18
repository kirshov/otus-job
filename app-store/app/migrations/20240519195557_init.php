<?php

declare(strict_types=1);

use Phoenix\Database\Element\ColumnSettings;
use Phoenix\Exception\InvalidArgumentValueException;
use Phoenix\Migration\AbstractMigration;

final class Init extends AbstractMigration
{
	/**
	 * @throws InvalidArgumentValueException
	 */
	protected function up(): void
	{
		$this->table('products')
			->addColumn('id', 'biginteger')
			->addColumn('name', 'string')
			->addColumn('image', 'string', [ColumnSettings::SETTING_DEFAULT => ''])
			->addColumn('price', 'integer')
			->addColumn('status', 'integer')
			->addColumn('quantity', 'integer')
			->addColumn('create_time', 'datetime')
			->create();

		$this->execute('CREATE SEQUENCE products_seq INCREMENT BY 1 MINVALUE 1 START 1');
	}

	protected function down(): void {
		$this->delete('products');
		$this->execute('DROP SEQUENCE products_seq');
	}
}