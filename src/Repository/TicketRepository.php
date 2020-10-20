<?php

namespace SimpleTicketing\Repository;

use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketId;

class TicketRepository extends DBALRepository
{
	private const TABLE_NAME = 'Ticket';

	/**
	 * @param TicketId $ticketId
	 *
	 * @return Ticket|null
	 */
	public function findById(TicketId $ticketId): ?Ticket
	{
		$stmt = $this->connection->createQueryBuilder()
			->select('id', 'authorId', 'status', 'assignedTo', 'createdOn', 'updatedOn')
			->from(self::TABLE_NAME)
			->where('id = ?')
			->setParameter(0, (string)$ticketId)
			->execute();

		if (!$result = $stmt->fetchAssociative()) {
			return null;
		}

		return Ticket::fromArray([
			'id' => $result['id'],
			'authorId' => $result['authorId'],
			'assignedTo' => $result['assignedTo'],
			'status' => $result['status'],
			'createdOn' => $result['createdOn'],
			'updatedOn' => $result['updatedOn']
		]);
	}

	/**
	 * @param Ticket $ticket
	 *
	 * @return TicketId
	 */
	public function insert(Ticket $ticket): TicketId
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

		return $ticket->id();
	}

	/**
	 * @param TicketId $ticketId
	 */
	public function deleteById(TicketId $ticketId)
	{
		$this->connection->createQueryBuilder()
			->delete(self::TABLE_NAME)
			->where('id = ?')
			->setParameter(0, (string)$ticketId)
			->execute();
	}
}
