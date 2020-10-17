<?php

namespace App\Ticket;

class Ticket
{
    private $id;
    private $authorId;
    private $status;
    private $assignedTo;

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
    	$ticket->id = uniqid();
    	$ticket->authorId = $authorId;
    	$ticket->status = 'NEW';
    	$ticket->assignedTo = null;

    	return $ticket;
    }
}
