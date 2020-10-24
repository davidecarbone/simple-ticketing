<?php

namespace SimpleTicketing\Tests\Unit\Ticket;

use PHPUnit\Framework\TestCase;
use SimpleTicketing\Ticket\ForbiddenTicketAssignationException;
use SimpleTicketing\Ticket\InvalidTicketStateException;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketMessage;
use SimpleTicketing\Ticket\TicketOwnershipException;
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

	    $user = $this->getUser();
	    $ticket = Ticket::createWithMessage(new TicketMessage('test', $user->id()));

	    $ticket->assignToUser($user);
    }

	/** @test */
	public function status_changes_automatically_when_assigned()
	{
		$admin = $this->getUser('ADMIN');

		$ticket = Ticket::createWithMessage(new TicketMessage('test', new UserId()));
		$ticket->assignToUser($admin);
		$ticketData = $ticket->toArray();

		$this->assertEquals(TicketStatus::ASSIGNED, $ticketData['status']);
	}

	/** @test */
	public function can_be_added_messages_by_users_owning_it_but_status_and_assignation_dont_change()
	{
		$user = $this->getUser('CUSTOMER');

		$ticket = Ticket::createWithMessage(new TicketMessage('test message', $user->id()));
		$ticket->addMessageForUser(new TicketMessage('new test message', $user->id()), $user);
		$ticketData = $ticket->toArray();

		$this->assertNull($ticketData['assignedTo']);
		$this->assertEquals('Nuovo', $ticketData['status']);
		$this->assertCount(2, $ticketData['messages']);
	}

	/** @test */
	public function cannot_be_added_messages_by_users_not_owning_it()
	{
		$this->expectException(TicketOwnershipException::class);

		$anotherUser = $this->getUser('CUSTOMER');

		$ticket = Ticket::createWithMessage(new TicketMessage('test message', new UserId()));
		$ticket->addMessageForUser(new TicketMessage('new test message', $anotherUser->id()), $anotherUser);
	}

	/** @test */
	public function if_status_is_new_can_be_added_messages_by_admins_and_status_and_assignation_change()
	{
		$admin = $this->getUser('ADMIN');

		$ticket = Ticket::createWithMessage(new TicketMessage('test message', new UserId()));
		$ticket->addMessageForUser(new TicketMessage('new test message', $admin->id()), $admin);
		$ticketData = $ticket->toArray();

		$this->assertEquals($admin->id(), $ticketData['assignedTo']);
		$this->assertEquals('Preso in carico', $ticketData['status']);
		$this->assertCount(2, $ticketData['messages']);
	}

	/** @test */
	public function if_status_is_assigned_can_be_added_messages_by_the_admin_which_it_is_assigned_to()
	{
		$admin = $this->getUser('ADMIN');

		$ticket = Ticket::createWithMessage(new TicketMessage('test message', new UserId()));
		$ticket->assignToUser($admin);
		$ticket->addMessageForUser(new TicketMessage('new test message', $admin->id()), $admin);
		$ticketData = $ticket->toArray();

		$this->assertCount(2, $ticketData['messages']);
	}

	/** @test */
	public function if_status_is_assigned_cannot_be_added_messages_by_admins_which_it_is_not_assigned_to()
	{
		$this->expectException(TicketOwnershipException::class);

		$admin = $this->getUser('ADMIN');
		$anotherAdmin = $this->getUser('ADMIN');

		$ticket = Ticket::createWithMessage(new TicketMessage('test message', new UserId()));
		$ticket->assignToUser($admin);
		$ticket->addMessageForUser(new TicketMessage('new test message', $anotherAdmin->id()), $anotherAdmin);
	}

	/** @test */
	public function if_status_is_closed_cannot_be_added_messages_by_users()
	{
		$this->expectException(InvalidTicketStateException::class);

		$user = $this->getUser();
		$ticket = Ticket::createWithMessage(new TicketMessage('test message', $user->id()));
		$ticket->closeByUser($user);
		$ticket->addMessageForUser(new TicketMessage('new test message', $user->id()), $user);
	}

	/** @test */
	public function if_status_is_closed_cannot_be_added_messages_by_admins()
	{
		$this->expectException(InvalidTicketStateException::class);

		$user = $this->getUser();
		$admin = $this->getUser('ADMIN');
		$ticket = Ticket::createWithMessage(new TicketMessage('test message', $user->id()));
		$ticket->closeByUser($user);
		$ticket->addMessageForUser(new TicketMessage('new test message', $admin->id()), $admin);
	}

	/** @test */
	public function can_be_closed_by_users_owning_it()
	{
		$user = $this->getUser('CUSTOMER');

		$ticket = Ticket::createWithMessage(new TicketMessage('test message', $user->id()));
		$ticket->closeByUser($user);
		$ticketData = $ticket->toArray();

		$this->assertEquals('Chiuso', $ticketData['status']);
	}

	/** @test */
	public function cannot_be_closed_by_users_not_owning_it()
	{
		$this->expectException(TicketOwnershipException::class);

		$anotherUser = $this->getUser('CUSTOMER');

		$ticket = Ticket::createWithMessage(new TicketMessage('test message', new UserId()));
		$ticket->closeByUser($anotherUser);
	}

	/**
	 * @param string        $type
	 * @param string|null   $userId
	 *
	 * @return User
	 */
	private function getUser($type = 'CUSTOMER', $userId = null)
	{
		if (null === $userId) {
			$userId = new UserId();
		}
		return User::fromArray([
			'id' => $userId,
			'username' => 'test',
			'password' => 'test',
			'type' => $type,
			'fullName' => 'user test'
		]);
	}
}
