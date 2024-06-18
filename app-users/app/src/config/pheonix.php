<?php
return [
	'log_table_name' => 'migrations',
	'migration_dirs' => [
		'first' => dirname(__DIR__, 2) . '/migrations',
	],
	'environments' => [
		'production' => [
			'adapter' => 'pgsql',
			'host' => $_ENV['POSTGRES_HOST'],
			'username' => $_ENV['POSTGRES_USER'],
			'password' => $_ENV['POSTGRES_PASSWORD'],
			'db_name' => $_ENV['POSTGRES_DB'],
			'charset' => 'utf8',
			'collation' => 'utf8mb4_general_ci',
		],
	],
	'default_environment ' => 'production',
];