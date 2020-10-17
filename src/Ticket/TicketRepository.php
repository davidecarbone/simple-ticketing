<?php

namespace App\Ticket;

use Doctrine\DBAL\Connection;

class TicketRepository
{
	private const TABLE_NAME = 'Ticket';

	private $connection;

	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	public function save(string $authorId)
	{
		$this->connection->createQueryBuilder()
			->insert(self::TABLE_NAME)
			->values([
				'authorId' => '?',
				'status' => '?',
				'assignedTo' => '?',
			])
			->setParameter(0, $authorId)
			->setParameter(1, 'NEW')
			->setParameter(2, null)
			->execute();
	}
}
