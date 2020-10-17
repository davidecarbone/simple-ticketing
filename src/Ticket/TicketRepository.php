<?php

namespace App\Ticket;

use App\DBALRepository;

class TicketRepository extends DBALRepository
{
	private const TABLE_NAME = 'Ticket';

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
