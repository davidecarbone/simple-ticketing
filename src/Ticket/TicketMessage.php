<?php

namespace SimpleTicketing\Ticket;

use SimpleTicketing\User\UserId;

final class TicketMessage
{
	/** @var string */
	private $message;

	/** @var UserId */
	private $authorId;

	/**
	 *
	 * @param string $message
	 * @param UserId $authorId
	 */
	public function __construct(string $message, UserId $authorId)
	{
		$this->message = $message;
		$this->authorId = $authorId;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'body' => $this->message,
			'authorId' => (string) $this->authorId
		];
	}

	/**
	 * @return UserId
	 */
	public function authorId(): UserId
	{
		return$this->authorId;
	}

	public function __toString()
	{
		return $this->message;
	}
}
