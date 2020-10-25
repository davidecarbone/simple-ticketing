<?php

namespace SimpleTicketing\Ticket;

use SimpleTicketing\Ticket\Exception\TicketStatusException;

final class TicketStatus
{
	public const NEW = 'Nuovo';
	public const ASSIGNED = 'Preso in carico';
	public const CLOSED = 'Chiuso';

	/** @var string */
	private $ticketStatus;

	/**
	 * @param string $ticketStatus
	 */
	public function __construct(string $ticketStatus)
	{
		if (!in_array($ticketStatus, [
			self::NEW,
			self::ASSIGNED,
			self::CLOSED
		])) {
			throw new TicketStatusException("$ticketStatus is not a valid ticket status.");
		}
		$this->ticketStatus = $ticketStatus;
	}

	public function __toString()
	{
		return $this->ticketStatus;
	}
}
