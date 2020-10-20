<?php

namespace SimpleTicketing\Ticket;

use SimpleTicketing\User\User;

class Ticket
{
	/** @var TicketId */
    private $id;

    /** @var User */
    private $author;

	/** @var User */
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
	 * @param User          $author
	 * @param TicketMessage $message
	 *
	 * @return Ticket
	 */
    public static function createWithAuthorAndMessage(User $author, TicketMessage $message): Ticket
    {
    	$ticket = new self;
    	$ticket->id = new TicketId();
    	$ticket->author = $author;
    	$ticket->assignedTo = null;
    	$ticket->status = new TicketStatus(TicketStatus::NEW);
    	$ticket->messages[] = $message;
    	$ticket->createdOn = (new \DateTime())->format('Y-m-d H:i:s');
    	$ticket->updatedOn = (new \DateTime())->format('Y-m-d H:i:s');

    	return $ticket;
    }

	/**
	 * @return array
	 */
    public function toArray(): array
    {
    	return [
    		'id' => $this->id,
		    'authorId' => $this->author->id(),
		    'assignedTo' => $this->assignedTo ? $this->assignedTo->fullName() : null,
		    'status' => $this->status,
		    'messages' => $this->messageList(),
		    'createdOn' => $this->createdOn,
		    'updatedOn' => $this->updatedOn
	    ];
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

		$this->assignedTo = $user;
		$this->status = TicketStatus::ASSIGNED;
	}
}
