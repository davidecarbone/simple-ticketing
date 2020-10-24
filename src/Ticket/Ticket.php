<?php

namespace SimpleTicketing\Ticket;

use SimpleTicketing\User\User;
use SimpleTicketing\User\UserId;

class Ticket implements \JsonSerializable
{
	/** @var TicketId */
    private $id;

	/** @var UserId */
	private $assignedTo;

	/** @var TicketStatus */
    private $status;

    /** @var TicketMessage[] */
    private $messages;

	/** @var \DateTime */
    private $createdOn;

	/** @var \DateTime */
    private $updatedOn;

    private function __construct()
    {
    }

	/**
	 * @param TicketMessage $message
	 *
	 * @return Ticket
	 */
    public static function createWithMessage(TicketMessage $message): Ticket
    {
    	$ticket = new self;
    	$ticket->id = new TicketId();
    	$ticket->assignedTo = null;
    	$ticket->status = new TicketStatus(TicketStatus::NEW);
    	$ticket->messages[] = $message;
    	$ticket->createdOn = (new \DateTime())->format('Y-m-d H:i:s');
    	$ticket->updatedOn = (new \DateTime())->format('Y-m-d H:i:s');

    	return $ticket;
    }

	/**
	 * @param array $ticketData
	 *
	 * @return Ticket
	 */
	public static function fromArray(array $ticketData): Ticket
	{
		$ticket = new self;
		$ticket->id = new TicketId($ticketData['id']);
		$ticket->assignedTo = $ticketData['assignedTo'];
		$ticket->status = new TicketStatus($ticketData['status']);
		$ticket->messages = $ticketData['messages'];
		$ticket->createdOn = (new \DateTime($ticketData['createdOn']))->format('Y-m-d H:i:s');
		$ticket->updatedOn = (new \DateTime($ticketData['updatedOn']))->format('Y-m-d H:i:s');

		return $ticket;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
    {
    	return [
    		'id' => (string)$this->id,
		    'assignedTo' => $this->assignedTo ? (string)$this->assignedTo : null,
		    'status' => (string)$this->status,
		    'messages' => $this->messageList(),
		    'createdOn' => $this->createdOn,
		    'updatedOn' => $this->updatedOn
	    ];
    }

	/**
	 * @return TicketId
	 */
    public function id()
    {
    	return $this->id;
    }

	/**
	 * @return array
	 */
    public function messageList(): array
    {
    	$messageList = [];

	    foreach ($this->messages as $message) {
		    $messageList[] = $message->toArray();
    	}

	    return $messageList;
    }

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
    public function belongsToUser(User $user): bool
    {
	    foreach ($this->messages as $message) {
		    if ((string)$message->authorId() === (string)$user->id()) {
		    	return true;
		    }
	    }

    	return false;
    }

	/**
	 * @param User $user
	 */
	public function assignToUser(User $user)
	{
		if (!$user->isAdmin()) {
			throw new ForbiddenTicketAssignationException('Cannot assign ticket to non-admin users.');
		}

		$this->assignedTo = $user->id();
		$this->status = TicketStatus::ASSIGNED;
	}

	/**
	 * @param TicketMessage $message
	 * @param User          $author
	 *
	 * @throws TicketOwnershipException
	 * @throws InvalidTicketStateException
	 */
	public function addMessageForUser(TicketMessage $message, User $author)
	{
		switch ((string)$this->status) {
			case TicketStatus::NEW:
				if (!$author->isAdmin() && !$this->belongsToUser($author)) {
					throw new TicketOwnershipException('Cannot add messages to non-owned tickets.');
				}
				break;

			case TicketStatus::ASSIGNED:
				if ($this->assignedTo !== $author->id() && !$this->belongsToUser($author)) {
					throw new TicketOwnershipException('Cannot add messages to non-owned tickets.');
				}
				break;

			case TicketStatus::CLOSED:
				throw new InvalidTicketStateException('Cannot add messages to closed tickets.');
				break;
		}

		if ($author->isAdmin()) {
			$this->assignToUser($author);
		}

		$this->messages[] = $message;
		$this->updatedOn = (new \DateTime())->format('Y-m-d H:i:s');
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}
}
