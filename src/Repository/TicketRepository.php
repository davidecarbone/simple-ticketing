<?php

namespace SimpleTicketing\Repository;

use SimpleTicketing\Ticket\Ticket;

class TicketRepository extends DBALRepository
{
	private const TABLE_NAME = 'Ticket';

	/**
	 * @param Ticket $ticket
	 */
	public function save(Ticket $ticket)
	{
		$ticketData = $ticket->toArray();

		$this->connection->createQueryBuilder()
			->insert(self::TABLE_NAME)
			->values([
				'id' => '?',
				'authorId' => '?',
				'status' => '?',
				'assignedTo' => '?',
				'createdOn' => '?',
				'updatedOn' => '?',
			])
			->setParameter(0, $ticketData['id'])
			->setParameter(1, $ticketData['authorId'])
			->setParameter(2, $ticketData['status'])
			->setParameter(3, $ticketData['assignedTo'])
			->setParameter(4, $ticketData['createdOn'])
			->setParameter(5, $ticketData['updatedOn'])
			->execute();
	}
}
