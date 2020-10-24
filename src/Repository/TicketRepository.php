<?php

namespace SimpleTicketing\Repository;

use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketId;
use SimpleTicketing\Ticket\TicketMessage;
use SimpleTicketing\User\UserId;

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

		$ticketResults = [];

		while ($result = $stmt->fetchAssociative()) {
			$ticketResults[] = $result;
		}

		if (empty($ticketResults)) {
			return null;
		}

		return $this->groupMessagesInSingleTicket($ticketResults);
	}

	/**
	 * @param UserId $userId
	 *
	 * @return Ticket[]
	 */
	public function findByUserId(UserId $userId): array
	{
		$stmt = $this->connection->createQueryBuilder()
			->select('Ticket.id', 'authorId', 'status', 'assignedTo', 'message', 'createdOn', 'updatedOn')
			->from(self::TABLE_NAME)
			->innerJoin(self::TABLE_NAME, 'TicketMessage', 'tm', 'tm.ticketId = Ticket.id')
			->where('tm.authorId = ?')
			->setParameter(0, (string)$userId)
			->execute();

		$ticketResults = [];

		while ($result = $stmt->fetchAssociative()) {
			$ticketResults[$result['id']][] = $result;
		}

		if (empty($ticketResults)) {
			return [];
		}

		return $this->groupMessagesInAllTickets($ticketResults);
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
				->setParameter(1, $ticketData['messages'][0]['authorId'])
				->setParameter(2, $ticketData['messages'][0]['body'])
				->execute();

			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollBack();
			throw $e;
		}

		return $ticket->id();
	}

	/**
	 * @param Ticket $ticket
	 *
	 * @return TicketId
	 * @throws \Exception
	 */
	public function update(Ticket $ticket): TicketId
	{
		$ticketData = $ticket->toArray();

		$this->connection->beginTransaction();

		try {
			$this->connection->createQueryBuilder()
				->update(self::TABLE_NAME)
				->set('status', '?')
				->set('assignedTo', '?')
				->set('updatedOn', '?')
				->where('id = ?')
				->setParameter(0, $ticketData['status'])
				->setParameter(1, $ticketData['assignedTo'])
				->setParameter(2, $ticketData['updatedOn'])
				->setParameter(3, $ticketData['id'])
				->execute();

			$this->connection->createQueryBuilder()
				->insert('TicketMessage')
				->values([
					'ticketId' => '?',
					'authorId' => '?',
					'message' => '?'
				])
				->setParameter(0, $ticketData['id'])
				->setParameter(1, end($ticketData['messages'])['authorId'])
				->setParameter(2, end($ticketData['messages'])['body'])
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

	/**
	 * @param array $ticketResults
	 *
	 * @return Ticket[]
	 */
	private function groupMessagesInAllTickets(array $ticketResults): array
	{
		$tickets = [];

		foreach ($ticketResults as $ticketId => $ticketMessages) {
			$tickets[] = $this->groupMessagesInSingleTicket($ticketMessages);
		}

		return $tickets;
	}

	/**
	 * @param array $ticketMessages
	 *
	 * @return Ticket
	 */
	private function groupMessagesInSingleTicket(array $ticketMessages): Ticket
	{
		$messages = [];

		foreach ($ticketMessages as $ticketId => $ticketMessage) {
			$messages[] = new TicketMessage($ticketMessage['message'], new UserId($ticketMessage['authorId']));
		}

		return Ticket::fromArray([
			'id' => $ticketMessage['id'],
			'status' => $ticketMessage['status'],
			'assignedTo' => $ticketMessage['assignedTo'],
			'messages' => $messages,
			'createdOn' => $ticketMessage['createdOn'],
			'updatedOn' => $ticketMessage['updatedOn']
		]);
	}
}
