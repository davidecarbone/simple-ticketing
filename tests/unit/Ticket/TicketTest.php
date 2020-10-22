<?php

namespace SimpleTicketing\Tests\Unit\Ticket;

use PHPUnit\Framework\TestCase;
use SimpleTicketing\Ticket\ForbiddenTicketAssignationException;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketMessage;
use SimpleTicketing\Ticket\TicketStatus;
use SimpleTicketing\User\User;
use SimpleTicketing\User\UserId;

class TicketTest extends TestCase
{
    /** @test */
    public function can_be_built_with_author_and_message_and_exported_to_array()
    {
    	$authorId = new UserId();
        $ticket = Ticket::createWithMessage(new TicketMessage('test message', $authorId));
        $ticketData = $ticket->toArray();

        $this->assertIsString($ticketData['id']);
        $this->assertNull($ticketData['assignedTo']);
        $this->assertEquals('Nuovo', $ticketData['status']);
        $this->assertEquals('test message', $ticketData['messages'][0]['body']);
        $this->assertEquals($authorId, $ticketData['messages'][0]['authorId']);
        $this->assertIsString($ticketData['createdOn']);
        $this->assertIsString($ticketData['updatedOn']);
    }

    public function cannot_be_assigned_to_non_admin_users()
    {
    	$this->expectException(ForbiddenTicketAssignationException::class);

	    $user = User::fromArray([
		    'id' => new UserId(),
		    'username' => 'test',
		    'password' => 'test',
		    'type' => 'CUSTOMER',
		    'fullName' => 'customer test'
	    ]);

	    $ticket = Ticket::createWithMessage(new TicketMessage('test', new UserId()));

	    $ticket->assignToUser($user);
    }

	/** @test */
	public function status_changes_automatically_when_assigned()
	{
		$admin = User::fromArray([
			'id' => new UserId(),
			'username' => 'test',
			'password' => 'test',
			'type' => 'ADMIN',
			'fullName' => 'admin test'
		]);

		$ticket = Ticket::createWithMessage(new TicketMessage('test', new UserId()));
		$ticket->assignToUser($admin);
		$ticketData = $ticket->toArray();

		$this->assertEquals(TicketStatus::ASSIGNED, $ticketData['status']);
	}
}
