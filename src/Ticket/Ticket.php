<?php

namespace SimpleTicketing\Ticket;

use SimpleTicketing\User\User;
use SimpleTicketing\User\UserId;

class Ticket
{
	/** @var TicketId */
    private $id;

    /** @var UserId */
    private $authorId;

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
	 * @param UserId        $authorId
	 * @param TicketMessage $message
	 *
	 * @return Ticket
	 */
    public static function createWithAuthorIdAndMessage(UserId $authorId, TicketMessage $message): Ticket
    {
    	$ticket = new self;
    	$ticket->id = new TicketId();
    	$ticket->authorId = $authorId;
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
		$ticket->authorId = new UserId($ticketData['authorId']);
		$ticket->assignedTo = $ticketData['assignedTo'];
		$ticket->status = new TicketStatus($ticketData['status']);
		$ticket->messages[] = '';
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
    		'id' => $this->id,
		    'authorId' => $this->authorId,
		    'assignedTo' => $this->assignedTo,
		    'status' => $this->status,
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
		    $messageList[] = (string) $message;
    	}

	    return $messageList;
    }

	/**
	 * @param User $user
	 */
	public function assignToUser(User $user)
	{
		if (!$user->isAdmin()) {
			throw new ForbiddenTicketAssignationException("Cannot assign ticket to non-admin users.");
		}

		$this->assignedTo = $user->id();
		$this->status = TicketStatus::ASSIGNED;
	}
}
