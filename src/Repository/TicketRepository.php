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
			->select('Ticket.id', 'authorId', 'status', 'assignedTo', 'message', 'createdOn', 'updatedOn')
			->from(self::TABLE_NAME)
			->innerJoin(self::TABLE_NAME, 'TicketMessage', 'tm', 'tm.ticketId = Ticket.id')
			->where('Ticket.id = ?')
			->setParameter(0, (string)$ticketId)
			->execute();

		if (!$result = $stmt->fetchAssociative()) {
			return null;
		}

		return Ticket::fromArray([
			'id' => $result['id'],
			'authorId' => $result['authorId'],
			'status' => $result['status'],
			'assignedTo' => $result['assignedTo'],
			'message' => $result['message'],
			'createdOn' => $result['createdOn'],
			'updatedOn' => $result['updatedOn']
		]);
	}

	/**
	 * @param Ticket $ticket
	 *
	 * @return TicketId
	 * @throws \Exception
	 */
	public function insert(Ticket $ticket): TicketId
	{
		$ticketData = $ticket->toArray();

		$this->connection->beginTransaction();

		try {
			$this->connection->createQueryBuilder()
				->insert(self::TABLE_NAME)
				->values([
					'id' => '?',
					'status' => '?',
					'assignedTo' => '?',
					'createdOn' => '?',
					'updatedOn' => '?',
				])
				->setParameter(0, $ticketData['id'])
				->setParameter(1, $ticketData['status'])
				->setParameter(2, $ticketData['assignedTo'])
				->setParameter(3, $ticketData['createdOn'])
				->setParameter(4, $ticketData['updatedOn'])
				->execute();

			$this->connection->createQueryBuilder()
				->insert('TicketMessage')
				->values([
					'ticketId' => '?',
					'authorId' => '?',
					'message' => '?'
				])
				->setParameter(0, $ticketData['id'])
				->setParameter(1, $ticketData['authorId'])
				->setParameter(2, $ticketData['messages'][0])
				->execute();

			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollBack();
			throw $e;
		}

		return $ticket->id();
	}

	/**
	 * @param TicketId $ticketId
	 *
	 * @throws \Exception
	 */
	public function deleteById(TicketId $ticketId)
	{
		$this->connection->beginTransaction();

		try {
			$this->connection->createQueryBuilder()
				->delete(self::TABLE_NAME)
				->where('id = ?')
				->setParameter(0, (string)$ticketId)
				->execute();

			$this->connection->createQueryBuilder()
				->delete('TicketMessage')
				->where('ticketId = ?')
				->setParameter(0, (string)$ticketId)
				->execute();

			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollBack();
			throw $e;
		}
	}
}
