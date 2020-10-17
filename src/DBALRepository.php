<?php

namespace App;

use Doctrine\DBAL\Connection;

abstract class DBALRepository
{
	protected $connection;

	/**
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}
}
