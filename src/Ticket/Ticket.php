<?php

namespace App\Ticket;

class Ticket
{
	/** @var TicketId */
    private $id;

    /** @var string */
    private $authorId;

	/** @var string */
	private $assignedTo;

	/** @var string */
    private $status;

	/** @var \DateTime */
    private $createdOn;

	/** @var \DateTime */
    private $updatedOn;

    private function __construct()
    {
    }

	/**
	 * @param string $authorId
	 *
	 * @return Ticket
	 */
    public static function createWithAuthorId(string $authorId): Ticket
    {
    	$ticket = new self;
    	$ticket->id = new TicketId();
    	$ticket->authorId = $authorId;
    	$ticket->assignedTo = null;
    	$ticket->status = 'NEW';
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
		    'authorId' => $this->authorId,
		    'assignedTo' => $this->assignedTo,
		    'status' => $this->status,
		    'createdOn' => $this->createdOn,
		    'updatedOn' => $this->updatedOn
	    ];
    }
}
