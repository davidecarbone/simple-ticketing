<?php

namespace SimpleTicketing\Ticket;

final class TicketMessage
{
	/** @var string */
	private $message;

	/**
	 *
	 * @param string $message
	 */
	public function __construct(string $message)
	{
		$this->message = $message;
	}

	public function __toString()
	{
		return $this->message;
	}
}
