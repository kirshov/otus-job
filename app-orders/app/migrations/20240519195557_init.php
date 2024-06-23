<?php

declare(strict_types=1);

use Phoenix\Database\Element\ColumnSettings;
use Phoenix\Exception\InvalidArgumentValueException;
use Phoenix\Migration\AbstractMigration;

final class Init extends AbstractMigration
{
	protected const ORDERS_TABLE = 'orders';
	protected const ORDERS_PRODUCT_TABLE = 'orders_product';
	protected const LOG_TABLE = 'event_log';
	/**
	 * @throws InvalidArgumentValueException
	 */
	protected function up(): void
	{
		$this->table(self::ORDERS_TABLE)
			->addColumn('id', 'biginteger')
			->addColumn('user_id', 'integer')
			->addColumn('status', 'integer')
			->addColumn('total', 'integer')
			->addColumn('idempotency_key', 'text', [
				ColumnSettings::SETTING_NULL => true,
				ColumnSettings::SETTING_DEFAULT => '""',
			])
			->addColumn('create_time', 'datetime')
			->create();

		$this->execute('CREATE SEQUENCE ' . self::ORDERS_TABLE . '_seq INCREMENT BY 1 MINVALUE 1 START 1');

		$this->table(self::ORDERS_PRODUCT_TABLE)
			->addColumn('id', 'biginteger')
			->addColumn('order_id', 'integer')
			->addColumn('product_id', 'integer')
			->addColumn('name', 'string')
			->addColumn('quantity', 'integer')
			->addColumn('price', 'integer')
			->create();

		$this->execute('CREATE SEQUENCE ' . self::ORDERS_PRODUCT_TABLE . '_seq INCREMENT BY 1 MINVALUE 1 START 1');

		$this->table(self::LOG_TABLE)
			->addColumn('id', 'biginteger')
			->addColumn('user_id', 'integer')
			->addColumn('order_id', 'integer')
			->addColumn('message', 'text')
			->addColumn('create_time', 'datetime')
			->create();

		$this->execute('CREATE SEQUENCE ' . self::LOG_TABLE . '_seq INCREMENT BY 1 MINVALUE 1 START 1');
	}

	protected function down(): void {
		$this->delete(self::ORDERS_TABLE);
		$this->execute('DROP SEQUENCE ' . self::ORDERS_TABLE . '_seq');

		$this->delete(self::ORDERS_PRODUCT_TABLE);
		$this->execute('DROP SEQUENCE ' . self::ORDERS_PRODUCT_TABLE . '_seq');

		$this->delete(self::LOG_TABLE);
		$this->execute('DROP SEQUENCE ' . self::LOG_TABLE . '_seq');
	}
}